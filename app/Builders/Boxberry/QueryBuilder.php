<?php

declare(strict_types=1);

namespace App\Builders\Boxberry;

use App\Builders\BaseBuilder;
use App\Enums\Boxberry\BoxberryUrlType;
use App\Enums\DeliveryType;
use App\Interfaces\RequestBuilderInterface;
use App\Services\LocationService;
use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\Log;

class QueryBuilder extends BaseBuilder implements RequestBuilderInterface
{
    private string $url;
    private string $token;

    private LocationService $locationService;

    public function __construct()
    {
        $this->url = config('companies.boxberry.url');
        $this->token = config('companies.boxberry.token');
        $this->locationService = new LocationService();

        // выявленные ограничения
        $this->limitWeight = (int) 999000;              // гр
        $this->limitLength = (int) 2000000000;          // см
        $this->limitWidth = (int) 2000000000;           // см
        $this->limitHeight = (int) 2000000000;          // см
        $this->limitInsurance = (float) 10000000;       // руб
        $this->limitCashOnDelivery = (float) 10000000;  // руб
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
            $fromTerminal = $this->locationService->location($request->from)->terminalsBoxberry()->first()->identifier;
            $toTerminal = $this->locationService->location($request->to)->terminalsBoxberry()->first()->identifier;
        } catch (\Throwable $th) {
            throw $th;
        }

        // проверка способа доставки, применение способа поумолчанию, если ни один не выбран
        $deliveryTypes = parent::checkDeliveryType($request);

        $supportedDeliveryMethods = [
            DeliveryType::Ss->value => 1,
            DeliveryType::Dd->value => 2,
        ];

        foreach ($deliveryTypes as $type) {

            // если способ доставки не поддерживается - переход к следующему
            if (!isset($supportedDeliveryMethods[$type])) {
                continue;
            }

            $BoxSizes = [];
            foreach ($request->places as $place) {

                $place = (object) $place;

                $gabarits = (object) [
                    'weight' => (int) $place->weight * 1000,    // вес, грамм
                    'length' => (int) $place->length,           // длина, см
                    'width' => (int) $place->width,             // ширина, см
                    'height' => (int) $place->height,           // высота, см
                ];

                // проверка габаритов
                try {
                    parent::checkGabarits($gabarits);
                } catch (\Throwable $th) {
                    throw $th;
                }

                $BoxSizes[] = [
                    "Weight" => $gabarits->weight,
                    "Depth" => $gabarits->length,
                    "Width" => $gabarits->width,
                    "Height" => $gabarits->height,
                ];
            }

            $template = [
                "token" => (string) $this->token,
                "method" => (string) BoxberryUrlType::DeliveryCalculation->value,
                "SenderCityId" => (string) $fromTerminal,
                "RecipientCityId" => (string) $toTerminal,
                "DeliveryType" => (string) $supportedDeliveryMethods[$type],
                "OrderSum" => (float) ($request->insurance ?? 0),
                "PaySum" => (float) ($request->cash_on_delivery ?? 0),
                "BoxSizes" => $BoxSizes,
                "Version" => "2.0"
            ];

            Log::channel('requests')->info("Отправка запроса: " . $this->url, $template);
            $pools[] = $pool->as($type)->post($this->url, $template);
        }

        return $pools;
    }
}
