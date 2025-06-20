<?php

namespace App\Builders\Nrg;

use App\Enums\DeliveryType;
use App\Enums\Nrg\NrgUrlType;
use App\Enums\Pek\PekTariffType;
use App\Enums\Pek\PekUrlType;
use App\Interfaces\QueryPoolBuilderInterface;
use App\Services\LocationService;
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
    ) {
        $this->url = config('companies.nrg.url');
        $this->token = config('companies.nrg.token');
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
        // если пользователь указал наложенный платёж - не следует продолжать выполнение
        try {
            $this->checkCashOnDelivery($request->cash_on_delivery);
        } catch (\Throwable $th) {
            throw new Exception('Проверка информации о наложенном платеже. ' . $th->getMessage());
            return [];
        }

        $from = $request->from;
        $to = $request->to;

        // если не обнаружен город, то нет смысла продолжать выполнение
        try {
            $fromTerminal = $this->locationService->fromNrg($from);
            $toTerminal = $this->locationService->fromNrg($to);
        } catch (\Throwable $th) {
            throw new Exception("Не удалось получить информацию о населённом пункте. " . $th->getMessage(), 500);
        }

        $places = $request->places;
        $insurancePrice = $request->insurance;

        // если не выбран способ доставки, то применяется способ поумолчанию
        $deliveryTypes = $this->chechDeliveryType($request->delivery_type);

        foreach ($deliveryTypes as $type) {

            $items = [];
            foreach ($places as $place) {
                $items[] = array_filter([
                    'weight' => (float) $place['weight'] ?? null,
                    'length' => (float) isset($place['length']) ? $place['length'] / 100 : null,
                    'width' => (float) isset($place['width']) ? $place['width'] / 100 : null,
                    'height' => (float) isset($place['height']) ? $place['height'] / 100 : null,
                    'volume' => (float) $place['volume'] ?? null
                ]);
            }

            $template = [
                "idCityFrom" => $fromTerminal->identifier,
                "idCityTo" => $toTerminal->identifier,
                "cover" => 0, // 1 - конверт, 0 - нет
                "idCurrency" => 0, // валюта
                "items" => $items, // позиции груза
                "declaredCargoPrice" => $insurancePrice ?  (float) $insurancePrice : 0, // объявленная ценность
                "idClient" => 0
            ];

            // dd($template);

            Log::channel('tk')->info("Отправка запроса: " . $this->url . NrgUrlType::Price->value, $template);

            $pools[] = $pool->as($type)->withHeaders(['NrgApi-DevToken' => $this->token])->post($this->url . NrgUrlType::Price->value, $template);
        }

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
}
