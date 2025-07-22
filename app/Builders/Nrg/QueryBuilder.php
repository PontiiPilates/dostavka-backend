<?php

namespace App\Builders\Nrg;

use App\Builders\BaseBuilder;
use App\Enums\Nrg\NrgUrlType;
use App\Interfaces\RequestBuilderInterface;
use App\Services\LocationService;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class QueryBuilder extends BaseBuilder implements RequestBuilderInterface
{
    private string $url;
    private string $token;

    private LocationService $locationService;

    public function __construct()
    {
        $this->url = config('companies.nrg.url') . NrgUrlType::Price->value;
        $this->token = config('companies.nrg.token');

        $this->locationService = new LocationService();

        // выявленные ограничения
        $this->limitWeight = (float) 999999999999;      // кг
        $this->limitLength = (float) 999999999999;      // см
        $this->limitWidth = (float) 999999999999;       // см
        $this->limitHeight = (float) 999999999999;      // см
        $this->limitInsurance = (float) 999999999999;   // руб
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

        // проверка наложенного платежа (не работает с нп)
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
            $from = $this->locationService->location($request->from)->terminalsNrg()->first();
            $to = $this->locationService->location($request->to)->terminalsNrg()->first();
        } catch (\Throwable $th) {
            throw $th;
        }

        // проверка способа доставки, применение способа поумолчанию, если ни один не выбран
        $deliveryTypes = parent::checkDeliveryType($request);

        foreach ($deliveryTypes as $type) {

            $items = [];
            foreach ($request->places as $place) {

                $place = (object) $place;

                // данная тк не реагирует на объём, api расчитывает его самостоятельно
                // поэтому его здесь нет
                // также тк допускает отсутствие параметров двш
                $gabarits = (object) [
                    'weight' => $place->weight,                                         // вес, кг
                    'length' => isset($place->length) ? $place->length / 100 : null,    // длина, м
                    'width' => isset($place->width) ? $place->width / 100 : null,       // ширина, м
                    'height' => isset($place->height) ? $place->height / 100 : null,    // высота, м
                ];

                // проверка габаритов
                try {
                    parent::checkGabarits($gabarits);
                } catch (\Throwable $th) {
                    throw $th;
                }

                // допускается отсутствие параметров дшв
                $items[] = array_filter([
                    'weight' => (float) $gabarits->weight ?? null,
                    'length' => (float) $gabarits->length ?? null,
                    'width' => (float) $gabarits->width ?? null,
                    'height' => (float) $gabarits->height ?? null,
                ]);
            }

            $template = [
                "idCityFrom" => (int) $from->identifier,
                "idCityTo" => (int) $to->identifier,
                "cover" => 0,                                               // 1 - конверт, 0 - нет
                "idCurrency" => 0,                                          // валюта
                "items" => (array) $items,                                  // позиции груза
                "declaredCargoPrice" => (float) isset($request->insurance)  // объявленная ценность
                    ? $request->insurance
                    : 0,
                "idClient" => 0,
            ];

            Log::channel('tk')->info("Отправка запроса: " . $this->url, $template);

            $pools[] = $pool->as($type)->withHeaders(['NrgApi-DevToken' => $this->token])->post($this->url, $template);
        }

        return $pools;
    }
}
