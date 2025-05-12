<?php

declare(strict_types=1);

namespace App\UseCases\TK;

use App\Dto\Boxberry\OfferDto;
use App\Dto\Boxberry\RequestParametersDto;
use App\Dto\Boxberry\ResponseCollectionDto;
use App\Enums\Boxberry\BoxberryUrlType;
use App\Enums\DeliveryType;
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
     * Возвращает расчет стоимости доставки. 
     */
    public function handle(Request $request): array
    {
        try {
            $this->from = $this->moreInfo($request->from);
            $this->to = $this->moreInfo($request->to);
        } catch (\Throwable $th) {
            return $this->errorResponse("Получение информации о населённм пункте:", [$th->getMessage()]);
        }

        $responses = Http::pool(fn(Pool $pool) => $this->pools($pool, $request));

        return $this->responseBuilder($responses);
    }

    /**
     * Возвращает массив запросов для параллельного выполнения.
     */
    private function pools(Pool $pool, Request $request): array
    {
        $pools = [];

        $deliveryMethods = $this->isDeliveryMethodSelected($request->delivery_methods);

        foreach ($deliveryMethods as $method) {

            $requestParameters = new RequestParametersDto(
                $this->token,
                BoxberryUrlType::DeliveryCalculation->value,
                $this->from->city_id_boxberry,
                $this->to->city_id_boxberry,
                $this->deliveryMethod($method),
                $request->sumoc,
                $this->places($request->places),
            );

            $pools[] = $pool->post($this->url, $requestParameters->toArray());
        }

        return $pools;
    }

    /**
     * Возвращает способ(ы) доставки, даже если он(они) не выбран(ы).
     */
    private function isDeliveryMethodSelected(array|null $methods): array
    {
        if (!$methods) {
            return [DeliveryType::Ss->value];
        }

        return $methods;
    }

    /**
     * Возвращает режим доставки в виде значения, которое может прочитать API.
     */
    private function deliveryMethod(string $method): int
    {
        $methods = [
            DeliveryType::Ss->value => 1,
            DeliveryType::Dd->value => 2,
        ];

        return $methods[$method];
    }

    /**
     * Возвращает режим доставки в виде значения, которое требуется для приложения.
     */
    private function deliveryMethodReverse(int $method): string
    {
        $methods = [
            1 => DeliveryType::Ss->value,
            2 => DeliveryType::Dd->value,
        ];

        return $methods[$method];
    }

    /**
     * Возвращает массив "мест" в формате, который требуется для API.
     */
    private function places(array $places): array
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
     */
    private function responseBuilder(array $responses): array
    {
        $responseCollection = new ResponseCollectionDto();

        foreach ($responses as $key => $response) {

            $response = $response->object();

            if ($response->error) {
                $response[$key][] = null;
                continue;
            }

            foreach ($response->result->DeliveryCosts as $item) {

                $mode = $this->deliveryMethodReverse($item->DeliveryTypeId);

                $offerDto = new OfferDto(null, $item->TotalPrice, $item->DeliveryPeriod);

                $responseCollection->setItem($mode, $offerDto->toArray());
            }
        }

        return $responseCollection->toArray();
    }
}
