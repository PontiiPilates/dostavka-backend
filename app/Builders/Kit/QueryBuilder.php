<?php

namespace App\Builders\Kit;

use App\Enums\DeliveryType;
use App\Enums\Kit\KitUrlType;
use App\Interfaces\QueryPoolBuilderInterface;
use App\Services\Location\LocationParserService;
use App\Services\Location\MultiLocationService;
use Exception;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Request;

class QueryBuilder implements QueryPoolBuilderInterface
{
    private string $url;
    private string $token;

    public function __construct(
        private LocationParserService $locationParser,
        private MultiLocationService $multiLocation,
    ) {
        $this->url = config('companies.kit.url') . KitUrlType::Calculate->value;
        $this->token = config('companies.kit.token');
    }

    /**
     * Обеспечивает сборку пула запросов для ассинхронной отправки.
     * 
     * @param Request $request
     * @return array
     */
    public function build(Request $request, Pool $pool): array|null
    {
        // если возникли проблемы с поиском населённого пункта - не следует продолжать выполнение
        try {
            $from = $this->multiLocation->city($request->from)->tkKitCity()->first();
            $to = $this->multiLocation->city($request->to)->tkKitCity()->first();
        } catch (\Throwable $th) {
            throw new Exception('Попытка получить данные о пункте отправки/доставки. ' . $th->getMessage());
            return [];
        }

        $places = $request->places;
        $declarePrice = $request->declare_price ?? 1; // обязательный параметр, который должен быть не меннее 1
        $cashOnDelivery = $request->cash_on_delivery;

        // если пользователь указал наложенный платёж - не следует продолжать выполнение
        try {
            $this->checkCashOnDelivery($cashOnDelivery);
        } catch (\Throwable $th) {
            throw new Exception('Проверка информации о наложенном платеже. ' . $th->getMessage());
            return [];
        }

        // если объявленная стоимость превышает установленные лимиты - не следует продолжать выполнение
        try {
            $this->checkDeclarePrice($declarePrice);
        } catch (\Throwable $th) {
            throw new Exception('Проверка лимита объявленной стоимости. ' . $th->getMessage());
            return [];
        }

        // если не выбран способ доставки, то применяется способ поумолчанию
        $deliveryTypes = $this->chechDeliveryType($request->delivery_type);

        foreach ($deliveryTypes as $type) {

            $places = [];
            foreach ($request->places as $place) {
                $places[] = array_filter([
                    'count_place' => '1', // количество мест в позиции
                    'weight' => $place['weight'] ?? null, // вес кг
                    'length' => $place['length'] ?? null, // длина см
                    'width' => $place['width'] ?? null, // ширина см
                    'height' => $place['height'] ?? null, // высота см
                    'volume' => $place['volume'] ?? null // объём м3
                ]);
                // при отправке volume параметры length, width, height можно не передавать и наоборот
            }

            $template = [
                'city_pickup_code' => $from->city_code, // откуда
                'city_delivery_code' => $to->city_code, // куда
                'declared_price' => $declarePrice, // объявленная стоимость груза
                'places' => $places,
                'pick_up' => $type == DeliveryType::Ds->value || $type == DeliveryType::Dd->value ? 1 : 0, // забор груза
                'delivery' => $type == DeliveryType::Sd->value || $type == DeliveryType::Dd->value ? 1 : 0, // доставка груза
            ];

            $pools[] = $pool->as($type)->withToken($this->token)->post($this->url, $template);
        }

        return $pools;
    }

    /**
     * Проверяет способ доставки. Возвращает способ доставки поумолчанию, если ни один не выбран.
     */
    private function chechDeliveryType(array|null $methods): array
    {
        if (!$methods) {
            return [DeliveryType::Ss->value];
        }

        return $methods;
    }

    /**
     * Проверяет сумму объявленной ценности. Выбрасывает исключение, если она больше установленного предела.
     */
    private function checkDeclarePrice($declarePrice): void
    {
        if ($declarePrice >= 50000) {
            throw new Exception('Сумма объявленной ценности больше установленной. Компания не будет участвовать в калькуляции.');
        }
    }

    /**
     * Проверяет наличие информации о наложенном платеже. Выбрасывает исключение, если она не указана. Допустима работа с нулевым значением.
     */
    private function checkCashOnDelivery($cashOnDelivery)
    {
        if (isset($cashOnDelivery) && $cashOnDelivery > 0) {
            throw new Exception('Компания не работает с наложенным платежём, поэтому не сможет участвовать в калькуляции.');
        }
    }
}
