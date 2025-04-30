<?php

declare(strict_types=1);

namespace App\UseCases\TK;

use App\Enums\Boxberry\BoxberryUrlType;
use App\Enums\DeliveryMethodsType;
use App\interfaces\CaseInterface;
use App\Models\City;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class BoxberryCase extends BaseCase implements CaseInterface
{
    private string $url;
    private string $token;
    private City $from;
    private City $to;

    public function __construct()
    {
        $this->url = config('companies.boxberry.url');
        $this->token = config('companies.boxberry.token');
    }

    /**
     * API Boxberry не требовательно к международности, тарифам, местам, режиму доставки, объявленной ценности.
     * Однако разделение по режиму доставки требуется для обеспечения бизнес-логики приложения.
     */
    public function handle(Request $request)
    {
        try {
            $this->from = $this->moreInfo($request->from);
            $this->to = $this->moreInfo($request->to);
        } catch (\Throwable $th) {
            return $this->errorResponse("Получение информации о населённм пункте", [$th->getMessage()]);
        }

        $responses = Http::pool(fn(Pool $pool) => $this->pools($pool, $request));

        return $this->responseBuilder($responses);
    }

    /**
     * Возвращает массив запросов для параллельного выполнения.
     */
    private function pools($pool, $request): array
    {
        $pools = [];

        $deliveryMethods = $this->isDeliveryMethodSelected($request->delivery_methods);

        foreach ($deliveryMethods as $method) {

            $dtoParameters = [
                'token' => $this->token,
                'method' => BoxberryUrlType::DeliveryCalculation->value,
                "SenderCityId" => $this->from->city_id_boxberry,
                "RecipientCityId" => $this->to->city_id_boxberry,
                "DeliveryType" => $this->deliveryMethod($method),
                "OrderSum" => $request->sumoc,
                "BoxSizes" => $this->places($request->places),
                "Version" => "2.2"
            ];

            // dump($dtoParameters);

            $pools[] = $pool->post($this->url, $dtoParameters);
        }

        return $pools;
    }

    /**
     * Возвращает способ(ы) доставки, даже если он(они) не выбран(ы).
     */
    private function isDeliveryMethodSelected(array|null $methods): array
    {
        if (!$methods) {
            return [DeliveryMethodsType::Ss->value];
        }

        return $methods;
    }

    /**
     * Возвращает режим доставки в виде значения, которое может прочитать API.
     */
    private function deliveryMethod($method): int
    {
        $methods = [
            DeliveryMethodsType::Ss->value => 1,
            DeliveryMethodsType::Dd->value => 2,
        ];

        return $methods[$method];
    }

    /**
     * Возвращает режим доставки в виде значения, которое требуется для приложения.
     */
    private function deliveryMethodReverse($method): string
    {
        $methods = [
            1 => DeliveryMethodsType::Ss->value,
            2 => DeliveryMethodsType::Dd->value,
        ];

        return $methods[$method];
    }

    /**
     * Возвращает массив "мест" в формате, который требуется для API.
     */
    private function places($places): array
    {
        $data = [];

        foreach ($places as $place) {
            $data[] = [
                'Weight' => $place['weight'] * 1000,
                'Length' => $place['length'],
                'Width' => $place['width'],
                'Height' => $place['height'],
            ];
        }

        return $data;
    }

    /**
     * Формирует ответ на основе специфики ответов API Boxberry.
     * 
     * Особенность данного API в том, что оно не возвращает ответов с кодом, кроме 200.
     * Ошибку распознать можно лишь внутри ответа: "error": true.
     */
    private function responseBuilder(array $responses): array
    {
        $dtoResponse = [];

        foreach ($responses as $key => $response) {

            $response = $response->object();

            if ($response->error) {
                $response[$key][] = null;
                continue;
            }

            foreach ($response->result->DeliveryCosts as $item) {

                $mode = $this->deliveryMethodReverse($item->DeliveryTypeId);

                $dtoResponse[$mode][] = [
                    'tariff' => null,
                    'cost' => $item->TotalPrice,
                    'days' => $item->DeliveryPeriod,
                ];
            }
        }

        return $dtoResponse;
    }
}
