<?php

namespace App\Builders\Dpd;

use App\Builders\BaseBuilder;
use App\Enums\DeliveryType;
use App\Enums\DPD\DpdUrlType;
use App\Models\Location;
use Exception;
use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\Log;

class QueryBuilder extends BaseBuilder
{
    private string $url;
    private string $clientNumber;
    private string $clientKey;

    public function __construct()
    {
        $this->url = config('companies.dpd.url') . DpdUrlType::Calculator->value;
        $this->clientNumber = config('companies.dpd.client_number');
        $this->clientKey = config('companies.dpd.client_key');

        // выявленные ограничения
        $this->limitWeight = 1000;        // вес, кг
        $this->limitLength = 350;         // длина, cм
        $this->limitWidth = 160;          // ширина, cм
        $this->limitHeight = 180;         // высота, cм
        $this->limitInsurance = 30000000; // объявленная ценность, руб
    }

    /**
     * Обеспечивает сборку запроса.
     * 
     * @param array $request
     * @param Pool $pool
     * 
     * @return array
     */
    public function build(array $request): array
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
            $from = Location::find($request->from)->terminalsDpd()->firstOrFail();
            $to = Location::find($request->to)->terminalsDpd()->firstOrFail();
        } catch (\Throwable $th) {
            throw new Exception("ТК не работает с локациями: $request->from -> $request->to", 200);
        }

        $places = [];
        foreach ($request->places as $place) {

            $place = (object) $place;

            $gabarits = (object) [
                'weight' => (int) $place->weight,    // вес, кг
                'length' => (int) $place->length,    // длина, см
                'width' => (int) $place->width,      // ширина, см
                'height' => (int) $place->height,    // высота, см
            ];

            // проверка габаритов
            try {
                parent::checkGabarits($gabarits);
            } catch (\Throwable $th) {
                throw $th;
            }

            $places[] = [
                "weight" => $gabarits->weight,
                "length" => $gabarits->length,
                "width" => $gabarits->width,
                "height" => $gabarits->height,
                "quantity" => 1,
            ];
        }

        // проверка способа доставки, применение способа поумолчанию, если ни один не выбран
        $deliveryTypes = parent::checkDeliveryType($request);

        $pool = [];
        foreach ($deliveryTypes as $type) {

            $template['request'] = [
                'declaredValue' => $request->insurance ?? 0, // объявленная ценность (итоговая)
                'parcel' => $places,
                'pickup' => [
                    'cityId' => $from->identifier, // откуда
                    'cityName' => $from->name, // откуда
                ],
                'delivery' => [
                    'cityId' => $to->identifier, // куда
                    'cityName' => $to->name, // куда
                ],
                'pickupDate' => $request->shipment_date, // дата сдачи груза
                'selfPickup' => $type == DeliveryType::Ds->value || $type == DeliveryType::Dd->value ? false : true,
                'selfDelivery' => $type == DeliveryType::Sd->value || $type == DeliveryType::Dd->value ? false : true,
                'auth' => [
                    'clientNumber' => $this->clientNumber,
                    'clientKey' => $this->clientKey,
                ]
            ];

            $pool[$type] = $template;

            Log::channel('requests')->info("Отправка запроса: " . $this->url, $template);
        }

        return $pool;
    }
}
