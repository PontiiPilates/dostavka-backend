<?php

declare(strict_types=1);

namespace App\Builders\Pochta;

use App\Builders\BaseBuilder;
use App\Enums\Pochta\PochtaUrlType;
use App\Interfaces\RequestBuilderInterface;
use App\Models\Location;
use App\Models\Tk\TariffPochta;
use Exception;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\Log;

class QueryBuilder extends BaseBuilder implements RequestBuilderInterface
{
    private string $url;

    private $weight;
    private $insurance;
    private $cashOnDelivery;

    public function __construct()
    {
        $this->url = config('companies.pochta.url') . PochtaUrlType::Calculate->value;

        // выявленные ограничения
        $this->limitInsurance = (float) 1000000000;         // руб
        $this->limitCashOnDelivery = (float) 1000000000000; // руб
    }

    /**
     * Обеспечивает сборку запросов для ассинхронной отправки.
     * 
     * Особенности работы:
     * первая особенность в том, что почта работает не с идентификаторами локаций а с индексами,
     * таким образом, индексы в базе уже должны от кого-то быть
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
            $from = Location::findOrFail($request->from);
            $to = Location::findOrFail($request->to);
        } catch (\Throwable $th) {
            throw new Exception("ТК не работает с локациями: $request->from -> $request->to", 200);
        }

        $place = collect($request->places);

        $this->weight = $place->sum('weight') * 1000;
        $this->insurance = isset($request->insurance) ? $request->insurance * 100 : null;
        $this->cashOnDelivery = isset($request->cash_on_delivery) ? $request->cash_on_delivery * 100 : null;

        // проверка способа доставки, применение способа поумолчанию, если ни один не выбран
        $deliveryTypes = $this->checkDeliveryType($request);

        foreach ($deliveryTypes as $type) {

            $tariffs = [];

            // в данной интеграции вместо проверок на ограничения используется выбор тарифов
            if (parent::checkInternational($from) || parent::checkInternational($to)) {
                $tariffs = $this->internationalTariffs($type);
            } else {
                $tariffs = $this->innerTariffs($type);
            }

            foreach ($tariffs as $tariff) {
                $template = array_filter([
                    'object' => $tariff->object,        // идентификатор объекта расчёта
                    'from' => $from->index_min,         // откуда
                    'to' => $to->index_min,             // куда
                    'weight' => $this->weight,          // вес отправления, гр
                    'sumoc' => $this->insurance,        // объявленная ценность, копеек
                    'sumnp' => $this->cashOnDelivery,   // наложенный платёж, копеек
                ]);

                $pools[] = $pool->as("$type:$tariff->object")->get($this->url, $template);
            }

            // если нет шаблона потому, что нет тарифов
            if (isset($template)) {
                Log::channel('requests')->info("Запрос на отправку: " . $this->url, $template);
            }
        }

        // если список тарифов оказался пуст
        if ($tariffs->isEmpty()) {
            throw new Exception("Не удалось подобрать тариф под заданные параметры", 200); // увидит пользователь
        }

        return $pools;
    }

    /**
     * Возвращает интернациональные тарифы.
     * 
     * @param string $type
     * @return EloquentCollection
     */
    private function internationalTariffs($type): EloquentCollection
    {
        $insurance = $this->insurance;
        $cashOnDelivery = $this->cashOnDelivery;

        return TariffPochta::query()
            ->where([
                ['country_to', '=', true],
                ['max_weight', '>=', $this->weight],
                [$type, '=', true],
            ])
            ->when($insurance, function ($query, $insurance) {
                $query->where('sumoc', true);
            })
            ->when($cashOnDelivery, function ($query, $cashOnDelivery) {
                $query->where('sumnp', true);
            })
            ->get();
    }

    /**
     * Возвращает внутренние тарифы.
     * 
     * @param string $type
     * @return array
     */
    private function innerTariffs($type): EloquentCollection
    {
        $insurance = $this->insurance;
        $cashOnDelivery = $this->cashOnDelivery;

        return TariffPochta::query()
            ->where([
                ['country_to', '=', false],
                ['max_weight', '>=', $this->weight],
                [$type, '=', true],
            ])
            ->when($insurance, function ($query, $insurance) {
                $query->where('sumoc', true);
            })
            ->when($cashOnDelivery, function ($query, $cashOnDelivery) {
                $query->where('sumnp', true);
            })
            ->get();
    }
}
