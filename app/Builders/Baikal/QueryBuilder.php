<?php

declare(strict_types=1);

namespace App\Builders\Baikal;

use App\Builders\BaseBuilder;
use App\Enums\Baikal\BaikalUrlType;
use App\Factorys\Baikal\DeliveryTypeFactory;
use App\Interfaces\RequestBuilderInterface;
use App\Services\LocationService;
use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\Log;

class QueryBuilder extends BaseBuilder implements RequestBuilderInterface
{
    private string $url;
    private string $username;

    private LocationService $locationService;

    public function __construct()
    {
        $this->url = config('companies.baikal.url');
        $this->username = config('companies.baikal.username');
        $this->locationService = new LocationService();

        // выявленные ограничения
        $this->limitWeight = (float) 20000;             // кг
        $this->limitVolume = (float) 72;                // м3
        $this->limitInsurance = (float) 10000000000000; // руб
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
            $this->checkCashOnDelivery($request);
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
            $fromTerminal = $this->locationService->location($request->from)->terminalsBaikal()->first()->identifier;
            $toTerminal = $this->locationService->location($request->to)->terminalsBaikal()->first()->identifier;
        } catch (\Throwable $th) {
            throw $th;
        }

        // проверка способа доставки, применение способа поумолчанию, если ни один не выбран
        $deliveryTypes = $this->checkDeliveryType($request);

        foreach ($deliveryTypes as $type) {

            $deliveryType = DeliveryTypeFactory::make($type, $fromTerminal, $toTerminal);

            $cargoList = [];
            foreach ($request->places as $place) {

                $place = (object) $place;

                $gabarits = (object) [
                    'weight' => (float) $place->weight,                                                             // кг
                    'length' => (float) $place->length / 100,                                                       // м
                    'width' => (float) $place->width / 100,                                                         // м
                    'height' => (float) $place->height / 100,                                                       // м
                    'volume' => (float) ($place->length / 100) * ($place->width / 100) * ($place->height / 100),    // м3
                ];

                // проверка габаритов
                try {
                    parent::checkGabarits($gabarits);
                } catch (\Throwable $th) {
                    throw $th;
                }

                $cargoList[] = [
                    "Weight" => $gabarits->weight,                          // вес груза, кг
                    "Length" => $gabarits->length,                          // длина груза, м
                    "Width" => $gabarits->width,                            // ширина груза, м
                    "Height" => $gabarits->height,                          // высота груза, м
                    "Volume" => $gabarits->volume,                          // объем груза, м3
                    "Units" => 1,                                           // количество мест
                    "Oversized" => 1,                                       // габарит (0 - габарит, 1 – негабарит)
                    "EstimatedCost" => (float) ($request->insurance ?? 0),  // оценочная стоимость груза, руб
                    'Services' => [],                                       // массив id услуг, из справочника
                ];
            }

            $template = $deliveryType;
            $template["Cargo"] = ["CargoList" => $cargoList];

            Log::channel('requests')->info("Отправка запроса: " . $this->url . BaikalUrlType::Calculator->value, $template);
            $pools[] = $pool->as($type)->withBasicAuth($this->username, '')->get($this->url . BaikalUrlType::Calculator->value, $template);
        }

        return $pools;
    }
}
