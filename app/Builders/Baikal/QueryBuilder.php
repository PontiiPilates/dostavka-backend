<?php

namespace App\Builders\Baikal;

use App\Enums\Baikal\BaikalUrlType;
use App\Enums\DeliveryType;
use App\Factorys\Baikal\DeliveryTypeFactory;
use App\Interfaces\RequestBuilderInterface;
use App\Services\LocationService;
use Exception;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class QueryBuilder implements RequestBuilderInterface
{
    private string $url;
    private string $username;

    private LocationService $locationService;

    public function __construct()
    {
        $this->url = config('companies.baikal.url');
        $this->username = config('companies.baikal.username');
        $this->locationService = new LocationService();
    }

    /**
     * Обеспечивает сборку запросов для ассинхронной отправки.
     * 
     * @param Pool $pool
     * @param Request $request
     * 
     * @return array
     */
    public function build(array $request, Pool $pool): array
    {
        $request = (object) $request;

        $places = $request->places;
        $insurancePrice = $request->insurance ?? 0;

        // если пользователь указал наложенный платёж - не следует продолжать выполнение
        try {
            $this->checkCashOnDelivery($request);
        } catch (\Throwable $th) {
            return [];
        }

        // если не обнаружен город, то нет смысла продолжать выполнение
        try {
            $fromTerminal = $this->locationService->location($request->from)->terminalsBaikal()->first()->identifier;
            $toTerminal = $this->locationService->location($request->to)->terminalsBaikal()->first()->identifier;
        } catch (\Throwable $th) {
            return [];
        }

        // если не выбран способ доставки, то применяется способ поумолчанию
        $deliveryTypes = $this->checkDeliveryType($request->delivery_type);
        foreach ($deliveryTypes as $type) {

            $deliveryType = DeliveryTypeFactory::make($type, $fromTerminal, $toTerminal);

            $cargoList = [];
            foreach ($places as $place) {
                $place = (object) $place;
                $weight = $place->weight;
                $length = $place->length / 100;
                $width = $place->width / 100;
                $height = $place->height / 100;

                $volume = $length * $width * $height;

                $cargoList[] = [
                    "Weight" => (float) $weight,                    // вес груза, кг
                    "Length" => (float) $length,                    // длина груза, м
                    "Width" => (float) $width,                      // ширина груза, м
                    "Height" => (float) $height,                    // высота груза, м
                    "Volume" => (float) $volume,                    // объем груза, м3
                    "Units" => 1,                                   // количество мест
                    "Oversized" => 1,                               // габарит (0 - габарит, 1 – негабарит)
                    "EstimatedCost" => (float) $insurancePrice,     // оценочная стоимость груза, руб
                    'Services' => [],                               // массив id услуг, из справочника
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
    private function checkCashOnDelivery($request)
    {
        if (isset($request->cash_on_delivery) && $request->cash_on_delivery > 0) {
            throw new Exception('Компания не работает с наложенным платежём, поэтому не сможет участвовать в калькуляции.', 200);
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
