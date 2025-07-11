<?php

namespace App\Builders\Cdek;

use App\Builders\BaseBuilder;
use App\Enums\Cdek\CdekUrlType;
use App\Interfaces\RequestBuilderInterface;
use App\Services\LocationService;
use App\Services\Tk\TokenCdekService;
use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\Log;

class QueryBuilder extends BaseBuilder implements RequestBuilderInterface
{
    private string $url;
    private string $token;

    private LocationService $locationService;
    private TokenCdekService $tokenCdecService;

    public function __construct()
    {
        $this->tokenCdecService = new TokenCdekService();
        $this->locationService = new LocationService();

        $this->url = config('companies.cdek.url') . CdekUrlType::TariffList->value;
        $this->token = $this->tokenCdecService->getActualToken();

        // выявленные ограничения
        $this->limitWeight = (int) 99900000;            // гр
        $this->limitLength = (int) 1000;                // см
        $this->limitWidth = (int) 1000;                 // см
        $this->limitHeight = (int) 1000;                // см
        $this->limitInsurance = (float) 1000000000000;  // руб
    }

    /**
     * Обеспечивает сборку пула запросов для ассинхронной отправки.
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
            $fromTerminal = $this->locationService->location($request->from)->terminalsCdek()->first()->identifier;
            $toTerminal = $this->locationService->location($request->to)->terminalsCdek()->first()->identifier;
        } catch (\Throwable $th) {
            throw $th;
        }

        $places = [];
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

            $places[] = [
                "weight" => $gabarits->weight,
                "length" => $gabarits->length,
                "width" => $gabarits->width,
                "height" => $gabarits->height,
            ];
        }

        $template = [
            "date" => (string) $request->shipment_date . 'T00:00:00+0000',
            "lang" => "rus",
            "from_location" => [
                "code" => (int) $fromTerminal
            ],
            "to_location" => [
                "code" => (int) $toTerminal
            ],
            "services" => [
                [
                    "code" => "INSURANCE",
                    "parameter" => (string) ($request->insurance ?? 0)
                ]
            ],
            "packages" => (array) $places,
        ];

        Log::channel('requests')->info("Отправка запроса: " . $this->url . CdekUrlType::TariffList->value, $template);
        $pools[] = $pool->withToken($this->token)->post($this->url, $template);

        return $pools;
    }
}
