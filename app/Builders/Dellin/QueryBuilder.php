<?php

namespace App\Builders\Dellin;

use App\Builders\BaseBuilder;
use App\Enums\DeliveryType;
use App\Enums\Dellin\DellinTariffType;
use App\Enums\Dellin\DellinUrlType;
use App\Factorys\Dellin\DeliveryTypeFactory;
use App\Interfaces\RequestBuilderInterface;
use App\Models\Location;
use Exception;
use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\Log;

class QueryBuilder extends BaseBuilder implements RequestBuilderInterface
{
    private string $url;
    private string $token;

    public function __construct()
    {
        $this->url = config('companies.dellin.url');
        $this->token = config('companies.dellin.token');

        // выявленные ограничения
        $this->limitWeight = 799;           // кг
        $this->limitLength = 6;             // м
        $this->limitWidth = 2.3;            // м
        $this->limitHeight = 2.25;          // м
        $this->limitVolume = 80;            // м
        $this->limitInsurance = 300000000;  // руб

        // ограничения для малогабаритного груза
        $this->smalllimitLength = 0.54;     // м
        $this->smallLimitWidth = 0.39;      // м
        $this->smallLimitHeight = 0.39;     // м
        $this->smallLimitVolume = 0.1;      // м3
        $this->smallLimitQuantity = 1;      // шт

        // ограничения для негабаритного груза
        $this->мaxLimitlength = 1.3;        // м
        $this->мaxLimitWidth = 1.0;         // м
        $this->мaxLimitHeight = 0.8;        // м
    }

    /**
     * Обеспечивает сборку запросов для ассинхронной отправки.
     * 
     * @param array $request
     * @param Pool $pool
     * 
     * @return array
     */
    public function build(array $request, Pool $pool): array
    {
        $request = (object) $request;

        // проверка наложенного платежа
        try {
            parent::checkCashOnDelivery($request);
        } catch (\Throwable $th) {
            throw $th;
        }

        // проверка объявленной ценности
        try {
            parent::checkDeclarePrice($request);
        } catch (\Throwable $th) {
            throw $th;
        }

        // проверка корректности получения идентификатора населённого пункта
        try {
            $from = Location::find($request->from)->terminalsDellin()->firstOrFail();
            $to = Location::find($request->to)->terminalsDellin()->firstOrFail();
        } catch (\Throwable $th) {
            throw new Exception("ТК не работает с локациями: $request->from -> $request->to", 200);
        }

        $places = collect($request->places);
        $maxLength = $places->max('length') / 100;                      // длина, м
        $maxWidth = $places->max('width') / 100;                        // ширина, м
        $maxHeight = $places->max('height') / 100;                      // высота, м
        $totalVolume = round(($maxLength * $maxWidth * $maxHeight), 3); // итоговый объём, м3
        $totalWeight = $places->sum('weight');                          // итоговый вес, кг

        // значение объёма не должно быть ниже минимально допустимого
        $totalVolume = $totalVolume < 0.001 ? 0.001 : $totalVolume;

        $gabarits = (object) [
            'weight' =>  (float) $totalWeight,
            'length' => (float) $maxLength,
            'width' => (float) $maxWidth,
            'height' => (float) $maxHeight,
            'totalVolume' => (float) $totalVolume,
            'totalWeight' => (float) $totalWeight,
        ];

        // проверка габаритов
        try {
            parent::checkGabarits($gabarits);
        } catch (\Throwable $th) {
            throw $th;
        }

        // проверка способа доставки, применение способа поумолчанию, если ни один не выбран
        $deliveryTypes = parent::checkDeliveryType($request);

        foreach ($deliveryTypes as $type) {

            // к небольшим параметрам груза можно подобрать соответствующий тариф
            // к остальным параметрам применяются остальные тарифы
            try {

                // если способ доставки не от двери до двери, то будут применены обычные тарифы
                if ($type != DeliveryType::Dd->value) {
                    throw new Exception("Тариф для малогабаритных грузов действует только в режиме доставки от двери до двери.", 200);
                }

                // проверка габаритов
                parent::checkSmallGabarits($gabarits);

                $tariffs = [
                    DellinTariffType::Small->value,
                ];
            } catch (\Throwable $th) {
                $tariffs = [
                    DellinTariffType::Auto->value,
                    DellinTariffType::Express->value,
                    DellinTariffType::Avia->value,
                ];
            }

            foreach ($tariffs as $tariff) {

                $delivery = DeliveryTypeFactory::make($type, $from, $to, $request->shipment_date, $tariff);

                // если груз негабаритный - требуется указание дополнительных прараметров
                try {
                    parent::checkNonGabarits($gabarits);
                } catch (\Throwable $th) {
                    $oversizeWeight = $gabarits->totalWeight;
                    $oversizeVolume = $gabarits->totalVolume;
                }

                $template = [
                    "appkey" => $this->token,
                    "delivery" => $delivery,
                    "cargo" => array_filter([
                        "quantity" => 1,                                            // количество мест (всегда 1, поскольку параметры макс. от всех мест)
                        "length" => $gabarits->length,                              // длина самого длинного грузового места, м
                        "width" => $gabarits->width,                                // ширина самого широкого грузового места, м
                        "height" => $gabarits->height,                              // высота самого высокого грузового места, м
                        "weight" => $gabarits->weight,                              // вес самого тяжелого грузового места, кг
                        "totalVolume" => $gabarits->totalVolume,                    // общий объём груза, м3
                        "totalWeight" => $gabarits->totalWeight,                    // общий вес  груза, кг
                        "oversizedWeight" => $oversizeWeight ?? null,
                        "oversizedVolume" => $oversizeVolume ?? null,
                        "insurance" => [
                            "statedValue" => (float) ($request->insurance ?? 0),    // объявленная стоимость груза
                            "term" => false                                         // страховка груза
                        ]
                    ]),
                    "payment" => [
                        "type" => "cash",
                        "paymentCity" => "7700000000000000000000000"                // один из группы обязателен
                    ]
                ];

                Log::channel('requests')->info("Отправка запроса: " . $this->url, $template);
                $pools[] = $pool->as($type . ":$tariff")->post($this->url . DellinUrlType::Calculator->value, $template);
            }
        }

        return $pools;
    }
}
