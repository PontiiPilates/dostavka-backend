<?php

namespace App\Builders\Jde;

use App\Builders\BaseBuilder;
use App\Enums\DeliveryType;
use App\Enums\Jde\JdeTariffType;
use App\Enums\Jde\JdeUrlType;
use App\Interfaces\RequestBuilderInterface;
use App\Services\LocationService;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Request;

class QueryBuilder extends BaseBuilder implements RequestBuilderInterface
{
    private string $url;
    // private string $token;
    // private string $user;

    private LocationService $locationService;

    public function __construct()
    {
        $this->locationService = new LocationService();

        $this->url = config('companies.jde.url') . JdeUrlType::Calculator->value;
        // $this->token = config('companies.jde.token');
        // $this->user = config('companies.jde.user');

        // выявленные ограничения
        $this->limitWeight = 100000;            // кг
        $this->limitLength = 250000;            // м
        $this->limitWidth = 10000;              // м
        $this->limitHeight = 10000;             // м
        $this->limitVolume = 250;               // м3
        $this->limitInsurance = 999999999999;   // руб
    }

    /**
     * Обеспечивает сборку пула запросов для ассинхронной отправки.
     * 
     * @param Request $request
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
            $from = $this->locationService->location($request->from)->terminalsJde()->first();
            $to = $this->locationService->location($request->to)->terminalsJde()->first();
        } catch (\Throwable $th) {
            throw $th;
        }

        $places = collect($request->places);
        $maxLength = $places->max('length') / 100;                      // длина, м
        $maxWidth = $places->max('width') / 100;                        // ширина, м
        $maxHeight = $places->max('height') / 100;                      // высота, м
        $totalVolume = round(($maxLength * $maxWidth * $maxHeight), 3); // итоговый объём, м3
        $totalWeight = $places->sum('weight');                          // итоговый вес, кг

        // значение объёма не должно быть ниже минимально допустимого
        $totalVolume = $totalVolume < 0.000001 ? 0.000001 : $totalVolume;

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

            $tariffs = [
                JdeTariffType::Combined->value,
                // JdeTariffType::Express->value,       // не обслуживается
                // JdeTariffType::Individual->value,    // не обслуживается
                // JdeTariffType::Internet->value,      // не обслуживается
                // JdeTariffType::Courier->value,       // не обслуживается
            ];

            foreach ($tariffs as $tariff) {

                $template = [
                    'from' => $from->identifier,
                    'to' => $to->identifier,
                    'weight' => $gabarits->totalWeight,                                                         // вес груза, кг
                    'length' => $gabarits->length,                                                              // длина самого габаритного места, м
                    'width' => $gabarits->width,                                                                // ширина самого габаритного места, м
                    'height' => $gabarits->height,                                                              // высота самого габаритного места, м
                    'volume' =>  $gabarits->totalVolume,                                                        // объём груза, м3
                    'quantity' => 1,                                                                            // количество мест (всегда 1, поскольку параметры макс. от всех мест)
                    'type' => $tariff,
                    'pickup' => $type == DeliveryType::Ds->value || $type == DeliveryType::Dd->value ? 1 : 0,   // забор груза 1 - да / 0 - нет
                    'delivery' => $type == DeliveryType::Sd->value || $type == DeliveryType::Dd->value ? 1 : 0, // доставка груза 1 - да / 0 - нет
                    'insValue' => $request->insurance ?? 0,                                                     // объявленная ценность
                    // 'user' => $this->user,                                                                   // не требуется
                    // 'token' => $this->token                                                                  // не требуется
                ];

                $pools[] = $pool->as($type . ":$tariff")->get($this->url, $template);
            }
        }

        return $pools;
    }
}
