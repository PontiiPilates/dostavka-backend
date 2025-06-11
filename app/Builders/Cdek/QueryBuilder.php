<?php

namespace App\Builders\Cdek;

use App\Enums\Cdek\CdekUrlType;
use App\Interfaces\QueryPoolBuilderInterface;
use App\Services\LocationService;
use App\Services\Tk\TokenCdekService;
use Exception;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class QueryBuilder implements QueryPoolBuilderInterface
{
    private string $url;
    private string $token;

    public function __construct(
        private LocationService $locationService,
        private TokenCdekService $tokenCdecService,
    ) {
        $this->url = config('companies.cdek.url') . CdekUrlType::TariffList->value;
        $this->token = $tokenCdecService->getActualToken();
    }

    /**
     * Обеспечивает сборку пула запросов для ассинхронной отправки.
     * 
     * @param Request $request
     * @return array
     */
    public function build(Request $request, Pool $pool): array|null
    {
        // если не обнаружен город, то нет смысла продолжать выполнение
        try {
            $fromTerminal = $this->locationService->fromCdek($request->from);
            $toTerminal = $this->locationService->fromCdek($request->to);
        } catch (\Throwable $th) {
            throw new Exception("Не удалось получить информацию о населённом пункте. " . $th->getMessage(), 500);
        }

        // если пользователь указал наложенный платёж - не следует продолжать выполнение
        try {
            $this->checkCashOnDelivery($request->cash_on_delivery);
        } catch (\Throwable $th) {
            throw new Exception('Проверка информации о наложенном платеже. ' . $th->getMessage());
            return [];
        }

        $places = [];
        foreach ($request->places as $place) {

            $weight = isset($place['weight'])
                ? (int) ($place['weight'] * 1000)
                : null;
            $length = isset($place['length'])
                ? (int) $place['length']
                : null;
            $width = isset($place['width'])
                ? (int) $place['width']
                : null;
            $height = isset($place['height'])
                ? (int) $place['height']
                : null;

            $places[] = array_filter([
                'weight' => $weight ?? null, // вес, грамм
                'length' => $length ?? null, // длина, см
                'width' => $width ?? null, // ширина, см
                'height' => $height ?? null, // высота, см
            ]);
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
                    "parameter" => (string) $request->insurance
                ]
            ],
            "packages" => (array) $places,
        ];

        Log::channel('tk')->info("Отправка запроса: " . $this->url . CdekUrlType::TariffList->value, $template);

        $pools[] = $pool->withToken($this->token)->post($this->url, $template);

        return $pools;
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
