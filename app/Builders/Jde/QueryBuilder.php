<?php

namespace App\Builders\Jde;

use App\Enums\DeliveryType;
use App\Enums\Jde\JdeTariffType;
use App\Enums\Jde\JdeUrlType;
use App\Interfaces\QueryPoolBuilderInterface;
use App\Services\Location\LocationParserService;
use App\Services\Location\MultiLocationService;
use Exception;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Request;

class QueryBuilder implements QueryPoolBuilderInterface
{
    private string $url;
    // private string $user;
    // private string $token;

    public function __construct(
        private LocationParserService $locationParser,
        private MultiLocationService $multiLocation,
    ) {
        $this->url = config('companies.jde.url') . JdeUrlType::Calculator->value;
        // $this->user = config('companies.jde.user'); // не требуется
        // $this->token = config('companies.jde.token'); // не требуется
    }

    /**
     * Обеспечивает сборку пула запросов для ассинхронной отправки.
     * 
     * @param Request $request
     * @return array
     */
    public function build(Request $request, Pool $pool): array|null
    {
        // если возникли проблемы с определением пунтктов приёма/выдачи - не следует продолжать выполнение
        try {
            $from = $this->multiLocation->specialFromJde($request->from, 'from')->terminal_id;
            $to = $this->multiLocation->specialFromJde($request->to, 'to')->terminal_id;
        } catch (\Throwable $th) {
            throw new Exception($th->getMessage(), 500);
            return [];
        }

        $places = collect($request->places);
        $quantity = $places->count();
        $weight = $places->max('weight');
        $length = $places->max('length') / 100;
        $width = $places->max('width') / 100;
        $height = $places->max('height') / 100;
        $totalVolume = round(($length * $width * $height), 2);
        $totalWeight = $weight * $quantity;
        $insValue = $request->sumoc;

        // если не выбран способ доставки, то применяется способ поумолчанию
        $deliveryTypes = $this->isDeliveryTypeUnselected($request->delivery_type);

        foreach ($deliveryTypes as $type) {

            $tariffs = [
                JdeTariffType::Combined->value,
                // JdeTariffType::Express->value, // не обслуживается
                // JdeTariffType::Individual->value, // не обслуживается
                // JdeTariffType::Internet->value, // не обслуживается
                // JdeTariffType::Courier->value, // не обслуживается
            ];

            foreach ($tariffs as $tariff) {

                $template = [
                    'from' => $from,
                    'to' => $to,
                    'weight' => $totalWeight,
                    'length' => $length,
                    'width' => $width,
                    'height' => $height,
                    'volume' => $totalVolume,
                    'quantity' => $quantity,
                    'type' => $tariff,
                    'pickup' => $type == DeliveryType::Ds->value || $type == DeliveryType::Dd->value ? 1 : 0,
                    'delivery' => $type == DeliveryType::Sd->value || $type == DeliveryType::Dd->value ? 1 : 0,
                    'insValue' => $insValue,
                    // 'user' => $this->user, // не требуется
                    // 'token' => $this->token // не требуется
                ];

                $pools[] = $pool->as($type . ":$tariff")->get($this->url, $template);
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
}
