<?php

namespace App\Builders\Dellin;

use App\Enums\DeliveryType;
use App\Enums\Dellin\DellinTariffType;
use App\Enums\Dellin\DellinUrlType;
use App\Factorys\Dellin\DeliveryTypeFactory;
use App\Interfaces\QueryPoolBuilderInterface;
use App\Services\Location\LocationParserService;
use Exception;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Request;

class QueryBuilder implements QueryPoolBuilderInterface
{
    private string $url;
    private string $token;

    public function __construct(
        private LocationParserService $locationParser,
    ) {
        $this->url = config('companies.dellin.url');
        $this->token = config('companies.dellin.token');
    }

    /**
     * Обеспечивает сборку запросов для ассинхронной отправки.
     * 
     * @param Pool $pool
     * @param Request $request
     * 
     * @return array
     */
    public function build(Request $request, Pool $pool): array|null
    {
        // если не обнаружен город, то нет смысла продолжать выполнение
        try {
            $from = $this->locationParser->moreAboutCity($request->from);
            $to = $this->locationParser->moreAboutCity($request->to);
        } catch (\Throwable $th) {
            throw new Exception("Не удалось получить информацию о населённом пункте. " . $th->getMessage(), 500);
            return null;
        }

        $places = collect($request->places);
        $quantity = $places->count();
        $weight = $places->max('weight');
        $length = $places->max('length') / 100;
        $width = $places->max('width') / 100;
        $height = $places->max('height') / 100;
        $totalVolume = round(($length * $width * $height), 2);
        $totalWeight = $weight * $quantity;
        $statedValue = $request->sumoc;
        $shipmentDate = $request->shipment_date;

        // если не выбран способ доставки, то применяется способ поумолчанию
        $deliveryTypes = $this->isDeliveryTypeUnselected($request->delivery_type);

        // если параметры груза превышают все допустимые габариты, то нет смысла продолжать выполнение
        try {
            $this->isOverCriticalSize($length, $width, $height);
        } catch (\Throwable $th) {
            return null;
        }

        foreach ($deliveryTypes as $type) {

            // к небольшим параметрам груза можно подобрать соответствующий тариф
            // к остальным параметрам применяются остальные тарифы
            try {
                $this->isNotSmall($type, $length, $width, $height, $totalVolume, $quantity);
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
                $delivery = DeliveryTypeFactory::make($type, $from, $to, $shipmentDate, $tariff);

                // если груз негабаритный, то требуется дополнительно оуказать его параметры
                try {
                    $this->isNonGabarit($length, $width, $height);
                } catch (\Throwable $th) {
                    $oversizeWeight = $totalWeight;
                    $oversizeVolume = $totalVolume;
                }

                $template = [
                    "appkey" => $this->token,
                    "delivery" => $delivery,
                    "cargo" => array_filter([
                        "quantity" => (int) $quantity, // количество грузовых мест
                        "length" => (float) $length, // длина самого длинного грузового места (м.)
                        "width" => (float) $width, // ширина самого широкого грузового места (м.)
                        "height" => (float) $height, // высота самого высокого грузового места (м.)
                        "weight" => (float) $weight, // вес самого тяжелого грузового места (кг.)
                        "totalVolume" => (float) $totalVolume, // общий объём груза (м3.)
                        "totalWeight" => (float) $totalWeight, // общий вес  груза (кг.)
                        "oversizedWeight" => $oversizeWeight ?? null,
                        "oversizedVolume" => $oversizeVolume ?? null,
                        "insurance" => [
                            "statedValue" => (float) $statedValue, // объявленная стоимость груза
                            "term" => false // страховка груза
                        ]
                    ]),
                    "payment" => [
                        "type" => "cash",
                        "paymentCity" => "7700000000000000000000000" // один из группы обязателен
                    ]
                ];

                $pools[] = $pool->as($type . ":$tariff")->post($this->url . DellinUrlType::Calculator->value, $template);
            }
        }

        return $pools;
    }

    /**
     * Возвращает способ доставки поумолчанию, если ни один не выбран.
     */
    private function isDeliveryTypeUnselected(array|null $methods): array
    {
        if (!$methods) {
            return [DeliveryType::Ss->value];
        }

        return $methods;
    }

    private function isNonGabarit($length, $width, $height)
    {
        // м
        $gabaritMaxlength = 1.3;
        $gabaritMaxWidth = 1.0;
        $gabaritMaxHeight = 0.8;

        if ($length > $gabaritMaxlength || $width > $gabaritMaxWidth || $height > $gabaritMaxHeight) {
            throw new Exception("Негабаритный груз");
        }
    }

    private function isOverCriticalSize($length, $width, $height)
    {
        // м
        $nonGabaritMaxlength = 6;
        $nonGabaritMaxWidth = 2.3;
        $nonGabaritMaxHeight = 2.25;

        if ($length > $nonGabaritMaxlength || $width > $nonGabaritMaxWidth || $height > $nonGabaritMaxHeight) {
            throw new Exception("Параметры груза превышают допустимые габариты");
        }
    }

    private function isNotSmall($type, $length, $width, $height, $totalVolume, $quantity)
    {
        if ($type != DeliveryType::Dd->value) {
            throw new Exception("Тариф не может быть применён к данному способу доставки");
        }

        // м
        $smallMaxlength = 0.54;
        $smallMaxWidth = 0.39;
        $smallMaxHeight = 0.39;
        $smallTotalVolume = 0.1;
        $smallQuantity = 1;

        if ($length > $smallMaxlength || $width > $smallMaxWidth || $height > $smallMaxHeight || $totalVolume > $smallTotalVolume || $quantity > $smallQuantity) {
            throw new Exception("Параметры груза превышают малогабаритные требования");
        }
    }
}
