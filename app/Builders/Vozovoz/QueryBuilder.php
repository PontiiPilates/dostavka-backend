<?php

namespace App\Builders\Vozovoz;

use App\Enums\DeliveryType;
use App\Enums\Vozovoz\VozovozUrlType;
use App\Factorys\Vozovoz\DeliveryTypeFactory;
use App\Services\Location\LocationParserService;
use Exception;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Request;

class QueryBuilder
{
    private string $url;
    private string $token;

    public function __construct(
        private LocationParserService $locationParser,
    ) {
        $this->url = config('companies.vozovoz.url');
        $this->token = config('companies.vozovoz.token');
    }

    /**
     * Обеспечивает сборку запросов для ассинхронной отправки.
     * 
     * @param Pool $pool
     * @param Request $request
     * 
     * @return array
     */
    public function build(Pool $pool, Request $request): array
    {
        try {
            $from = $this->locationParser->moreAboutCity($request->from);
            $to = $this->locationParser->moreAboutCity($request->to);
        } catch (\Throwable $th) {
            throw new Exception("Не удалось получить информацию о населённом пункте. " . $th->getMessage(), 500);
        }

        $deliveryTypes = $this->isDeliveryTypeUnselected($request->delivery_type);
        $shipmentDate = $request->shipment_date;
        $places = $request->places;
        $url = $this->url . '?' . "token=$this->token";

        foreach ($deliveryTypes as $type) {
            $gateway = DeliveryTypeFactory::make($type, $from, $to, $shipmentDate);

            $wizard = [];
            foreach ($places as $place) {
                $wizard[] = [
                    "length" => (float) $place["length"] * 0.01,
                    "width" => (float) $place["width"] * 0.01,
                    "height" => (float) $place["height"] * 0.01,
                    "quantity" => (int) 1,
                    "weight" => (float) $place["weight"],
                ];
            }

            $template = [
                'object' => VozovozUrlType::Price->value,
                'action' => 'get',
                'params' => [
                    "cargo" => [
                        "wizard" => $wizard
                    ],
                    "gateway" => $gateway
                ]
            ];

            $pools[] = $pool->as($type)->post($url, $template);
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
