<?php

namespace App\Builders\Vozovoz;

use App\Builders\BaseBuilder;
use App\Enums\Vozovoz\VozovozUrlType;
use App\Factorys\Vozovoz\DeliveryTypeFactory;
use App\Interfaces\RequestBuilderInterface;
use App\Models\Location;
use Exception;
use Illuminate\Http\Client\Pool;

class QueryBuilder extends BaseBuilder implements RequestBuilderInterface
{
    private string $url;

    public function __construct()
    {
        $this->url = config('companies.vozovoz.url') . '?token=' . config('companies.vozovoz.token');

        // выявленные ограничения
        $this->limitWeight = (float) 19999;         // кг
        $this->limitLength = (float) 12.89;         // м
        $this->limitWidth = (float) 2.39;           // м
        $this->limitHeight = (float) 2.39;          // м
        $this->limitVolume = (float) 79.9;          // м3
        $this->limitInsurance = (float) 99999999;   // руб
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
            $this->checkCashOnDelivery($request);
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
            $from = Location::find($request->from)->terminalsVozovoz()->firstOrFail();
            $to = Location::find($request->to)->terminalsVozovoz()->firstOrFail();
        } catch (\Throwable $th) {
            throw new Exception("ТК не работает с локациями: $request->from -> $request->to", 200);
        }

        $places = collect($request->places);
        $maxLength = $places->max('length') * 0.01;     // длина, м
        $maxWidth = $places->max('width') * 0.01;       // ширина, м
        $maxHeight = $places->max('height') * 0.01;     // высота, м
        $maxWeight = $places->max('weight');            // вес, кг
        $totalVolume = $places->sum('volume');          // итоговый объём, м3
        $totalWeight = $places->sum('weight');          // итоговый вес, кг
        $quantity = $places->count();                   // общее количество мест

        $gabarits = (object) [
            'weight' => $totalWeight,
            'length' => $maxLength,
            'width' => $maxWidth,
            'height' => $maxHeight,
            'totalVolume' => $totalVolume,
            'totalWeight' => $totalWeight,
            'quantity' => $quantity,
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
            $gateway = DeliveryTypeFactory::make($type, $from, $to, $request->shipment_date);

            $template = [
                'object' => VozovozUrlType::Price->value,
                'action' => 'get',
                'params' => [
                    'cargo' => array_filter([
                        'dimension' => [
                            'max' => [
                                'weight' => (float) $maxWeight,
                                'length' => (float) $maxLength,
                                'width' => (float) $maxWidth,
                                'height' => (float) $maxHeight,
                            ],
                            'quantity' => (int) $gabarits->quantity,
                            'volume' => (float) $gabarits->totalVolume,
                            'weight' => (float) $gabarits->totalWeight,
                        ],
                        'insurance' => isset($request->insurance) ? (float) $request->insurance : null,
                    ]),
                    'gateway' => $gateway
                ]
            ];

            $pools[] = $pool->as($type)->post($this->url, $template);
        }

        return $pools;
    }
}
