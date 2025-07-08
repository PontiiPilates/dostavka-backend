<?php

declare(strict_types=1);

namespace App\Builders\Kit;

use App\Builders\BaseBuilder;
use App\Enums\DeliveryType;
use App\Enums\Kit\KitUrlType;
use App\Interfaces\RequestBuilderInterface;
use App\Services\LocationService;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class QueryBuilder extends BaseBuilder implements RequestBuilderInterface
{
    private string $url;
    private string $token;

    private LocationService $locationService;

    public function __construct()
    {
        $this->url = config('companies.kit.url') . KitUrlType::Calculate->value;
        $this->token = config('companies.kit.token');
        $this->locationService = new LocationService();
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

        $places = $request->places;
        $declarePrice = $request->insurance ?? 1; // обязательный параметр, который должен быть не меннее 1 (руб)

        // если пользователь указал наложенный платёж - не следует продолжать выполнение
        try {
            $this->checkCashOnDelivery($request);
        } catch (\Throwable $th) {
            return [];
        }

        // если не обнаружен город - не следует продолжать выполнение
        try {
            $fromTerminal = $this->locationService->location($request->from)->terminalsKit()->first()->identifier;
            $toTerminal = $this->locationService->location($request->to)->terminalsKit()->first()->identifier;
        } catch (\Throwable $th) {
            return [];
        }

        // если объявленная стоимость превышает установленные лимиты - не следует продолжать выполнение
        try {
            $this->checkDeclarePrice($declarePrice);
        } catch (\Throwable $th) {
            return [];
        }

        // если не выбран способ доставки - применяется способ поумолчанию
        $deliveryTypes = $this->checkDeliveryType($request);

        foreach ($deliveryTypes as $type) {

            $places = [];
            foreach ($request->places as $place) {
                $place = (object) $place;
                $places[] = array_filter([
                    'count_place' => '1',                   // количество мест в позиции
                    'weight' => $place->weight ?? null,     // вес кг
                    'length' => $place->length ?? null,     // длина см
                    'width' => $place->width ?? null,       // ширина см
                    'height' => $place->height ?? null,     // высота см
                    'volume' => $place->volume ?? null      // объём м3
                ]);
                // в данной интеграции при отправке volume параметры length, width, height можно не передавать и наоборот
                // поэтому все они могут быть null
            }

            $template = [
                'city_pickup_code' => $fromTerminal,                                                            // откуда
                'city_delivery_code' => $toTerminal,                                                            // куда
                'declared_price' => $declarePrice,                                                              // объявленная стоимость груза
                'places' => $places,
                'pick_up' => $type == DeliveryType::Ds->value || $type == DeliveryType::Dd->value ? 1 : 0,      // забор груза
                'delivery' => $type == DeliveryType::Sd->value || $type == DeliveryType::Dd->value ? 1 : 0,     // доставка груза
            ];

            Log::channel('requests')->info("Отправка запроса: " . $this->url . KitUrlType::Calculate->value, $template);
            $pools[] = $pool->as($type)->withToken($this->token)->post($this->url, $template);
        }

        return $pools;
    }
}
