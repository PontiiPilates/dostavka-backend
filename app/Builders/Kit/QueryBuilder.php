<?php

declare(strict_types=1);

namespace App\Builders\Kit;

use App\Builders\BaseBuilder;
use App\Enums\DeliveryType;
use App\Enums\Kit\KitUrlType;
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
        $this->url = config('companies.kit.url') . KitUrlType::Calculate->value;
        $this->token = config('companies.kit.token');

        // выявленные ограничения
        $this->limitWeight = (int) 1000000000;      // кг
        $this->limitLength = (int) 1000000000;      // см
        $this->limitWidth = (int) 1000000000;       // см
        $this->limitHeight = (int) 1000000000;      // см
        $this->limitVolume = (float) 100000000;     // м3
        $this->limitInsurance = (int) 50000;        // руб
    }

    /**
     * Обеспечивает сборку пула запросов для ассинхронной отправки.
     * 
     * @param array $request
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
            $from = Location::find($request->from)->terminalsKit()->firstOrFail();
            $to = Location::find($request->to)->terminalsKit()->firstOrFail();
        } catch (\Throwable $th) {
            throw new Exception("ТК не работает с локациями: $request->from -> $request->to", 200);
        }

        // проверка способа доставки, применение способа поумолчанию, если ни один не выбран
        $deliveryTypes = $this->checkDeliveryType($request);

        foreach ($deliveryTypes as $type) {

            $places = [];
            foreach ($request->places as $place) {

                $place = (object) $place;

                $gabarits = (object) [
                    'weight' => (int) $place->weight,   // вес, грамм
                    'length' => (int) $place->length,   // длина, см
                    'width' => (int) $place->width,     // ширина, см
                    'height' => (int) $place->height,   // высота, см
                    'volume' => (float) $place->volume, // м3
                ];

                // проверка габаритов
                try {
                    parent::checkGabarits($gabarits);
                } catch (\Throwable $th) {
                    throw $th;
                }

                $places[] = array_filter([
                    'count_place' => '1',                   // количество мест в позиции
                    'weight' => $gabarits->weight ?? null,  // вес кг
                    'length' => $gabarits->length ?? null,  // длина см
                    'width' => $gabarits->width ?? null,    // ширина см
                    'height' => $gabarits->height ?? null,  // высота см
                    'volume' => $gabarits->volume ?? null   // объём м3
                ]);
                // в данной интеграции при отправке volume параметры length, width, height можно не передавать и наоборот
                // поэтому все они могут быть null
            }

            $template = [
                'city_pickup_code' => $from->identifier,                                                        // откуда
                'city_delivery_code' => $to->identifier,                                                        // куда
                'declared_price' => (int) ($request->insurance ?? 1),                                       // объявленная стоимость груза
                'places' => $places,
                'pick_up' => $type == DeliveryType::Ds->value || $type == DeliveryType::Dd->value ? 1 : 0,  // забор груза
                'delivery' => $type == DeliveryType::Sd->value || $type == DeliveryType::Dd->value ? 1 : 0, // доставка груза
            ];

            Log::channel('requests')->info("Отправка запроса: " . $this->url . KitUrlType::Calculate->value, $template);
            $pools[] = $pool->as($type)->withToken($this->token)->post($this->url, $template);
        }

        return $pools;
    }
}
