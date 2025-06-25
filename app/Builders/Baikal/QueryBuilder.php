<?php

namespace App\Builders\Baikal;

use App\Enums\Baikal\BaikalUrlType;
use App\Enums\DeliveryType;
use App\Factorys\Baikal\DeliveryTypeFactory;
use App\Interfaces\QueryPoolBuilderInterface;
use App\Services\LocationService;
use Exception;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class QueryBuilder implements QueryPoolBuilderInterface
{
    private string $url;
    private string $username;

    public function __construct(
        private LocationService $locationService,
    ) {
        $this->url = config('companies.baikal.url');
        $this->username = config('companies.baikal.username');
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
            $fromTerminal = $this->locationService->location($from)->terminalsBaikal()->first()->identifier;
            $toTerminal = $this->locationService->location($to)->terminalsBaikal()->first()->identifier;
        } catch (\Throwable $th) {
            throw new Exception("Не удалось получить информацию о населённом пункте. " . $th->getMessage(), 500);
        }

        $places = $request->places;
        $insurancePrice = $request->insurance;

        // если не выбран способ доставки, то применяется способ поумолчанию
        $deliveryTypes = $this->checkDeliveryType($request->delivery_type);

        foreach ($deliveryTypes as $type) {

            $deliveryType = DeliveryTypeFactory::make($type, $fromTerminal, $toTerminal);

            $cargoList = [];
            foreach ($places as $place) {

                $weight = $place['weight'];
                $length = $place['length'] / 100;
                $width = $place['width'] / 100;
                $height = $place['height'] / 100;

                $volume = $length * $width * $height;

                $cargoList[] = [
                    "Weight" => (float) $weight, // вес груза (в килограммах)
                    "Length" => (float) $length, // длина груза (в метрах)
                    "Width" => (float) $width, // ширина груза (в метрах)
                    "Height" => (float) $height, // высота груза (в метрах)
                    "Volume" => (float) $volume, // объем груза (в кубических метрах)
                    "Units" => 1, // количество мест
                    "Oversized" => 1, // габарит (0 - габарит, 1 – негабарит)
                    "EstimatedCost" => (float) $insurancePrice, // оценочная стоимость груза (в рублях)
                    'Services' => [], // массив id услуг из справочника
                ];
            }

            $template = $deliveryType;
            $template["Cargo"] = ["CargoList" => $cargoList];

            Log::channel('tk')->info("Отправка запроса: " . $this->url . BaikalUrlType::Calculator->value, $template);

            $pools[] = $pool->as($type)->withBasicAuth($this->username, '')->get($this->url . BaikalUrlType::Calculator->value, $template);
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
    private function checkDeliveryType(array|null $methods): array
    {
        if (!$methods) {
            return [DeliveryType::Ss->value];
        }

        return $methods;
    }
}
