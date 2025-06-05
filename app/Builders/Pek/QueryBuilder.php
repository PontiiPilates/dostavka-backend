<?php

namespace App\Builders\Pek;

use App\Enums\DeliveryType;
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
    private string $user;
    private string $password;

    public function __construct(
        private LocationService $locationService,
    ) {
        $this->url = config('companies.pek.url');
        $this->user = config('companies.pek.user');
        $this->password = config('companies.pek.password');
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
        $from = $request->from;
        $to = $request->to;

        $constraints = $this->constraints($request);

        // если не обнаружен город, то нет смысла продолжать выполнение
        try {
            $fromTerminal = $this->locationService->tkPek($from, $constraints);
            $toTerminal = $this->locationService->tkPek($to, $constraints);
        } catch (\Throwable $th) {
            throw new Exception("Не удалось получить информацию о населённом пункте. " . $th->getMessage(), 500);
        }

        $fromCity = mb_ucfirst(mb_strtolower($fromTerminal->city->city_name));
        $toCity = mb_ucfirst(mb_strtolower($toTerminal->city->city_name));
        $fromCountry = mb_ucfirst(mb_strtolower($fromTerminal->city->country->name));
        $toCountry = mb_ucfirst(mb_strtolower($toTerminal->city->country->name));

        $places = $request->places;
        $shipmentDate = $request->shipment_date;
        $insurancePrice = $request->sumoc;

        // если не выбран способ доставки, то применяется способ поумолчанию
        $deliveryTypes = $this->isDeliveryTypeUnselected($request->delivery_type);

        $tariffs = collect([]);

        try {
            $this->checkCargo($places, 'avia');
            $tariffs->push(
                PekTariffType::AviaExpress->value
            );
        } catch (\Throwable $th) {
            // авиа-тарифы не будут принимать участие в калькуляции
        }

        // если габариты превышают любые допустимые, то нет смысла продолжать выполнение
        try {
            $this->checkCargo($places, 'auto');
            $tariffs->push(
                PekTariffType::Auto->value,
                PekTariffType::AutoDts->value,
                PekTariffType::AutoEasyWay->value
            );
        } catch (\Throwable $th) {
            throw new Exception("Параметры груза превышают допустимые габариты.", 500);
        }

        foreach ($deliveryTypes as $type) {

            $cargos = [];
            foreach ($places as $place) {
                $cargos[] = array_filter([
                    'weight' => (float) $place['weight'] ?? null,
                    'length' => (float) isset($place['length']) ? $place['length'] / 100 : null,
                    'width' => (float) isset($place['width']) ? $place['width'] / 100 : null,
                    'height' => (float) isset($place['height']) ? $place['height'] / 100 : null,
                    'volume' => (float) $place['volume'] ?? null
                ]);
            }

            $template = [
                "types" => $tariffs->toArray(),
                "senderWarehouseId" => $fromTerminal->terminal_id,
                "receiverWarehouseId" => $toTerminal->terminal_id,
                "plannedDateTime" => $shipmentDate . 'T00:00:00',
                "isInsurance" => $insurancePrice ? true : false,
                "isInsurancePrice" => $insurancePrice ?  $insurancePrice : 0.0,
                'isPickUp' => $type == DeliveryType::Ds->value || $type == DeliveryType::Dd->value ? true : false,
                'isDelivery' => $type == DeliveryType::Sd->value || $type == DeliveryType::Dd->value ? true : false,
                "pickup" => ["address" => "$fromCountry, $fromCity"],
                "delivery" => ["address" => "$toCountry, $toCity"],
                "cargos" => $cargos,
            ];

            Log::channel('tk')->info("Отправка запроса: " . $this->url . PekUrlType::Calculate->value, $template);

            $pools[] = $pool->as($type)->withBasicAuth($this->user, $this->password)->post($this->url . PekUrlType::Calculate->value, $template);
        }

        return $pools;
    }

    /**
     * Возвращает способ доставки поумолчанию, если ни один не выбран.
     */
    private function isDeliveryTypeUnselected(array|null $methods): array
    {
        if (!$methods) {
            return [DeliveryType::Ss->value];
        }

        return $methods;
    }

    private function constraints(Request $request)
    {
        $totalWeight = 0;
        $totalVolume = 0;
        $maxWeightPerPlace = 0;
        $maxDimension = collect([]);

        foreach ($request->places as $place) {

            $totalWeight += $place['weight'];

            if ($place['weight'] > $maxWeightPerPlace) {
                $maxWeightPerPlace = $place['weight'];
            }

            $maxDimension->push(collect([
                $place['length'] ?? 0,
                $place['width'] ?? 0,
                $place['height'] ?? 0
            ])->max());
        }

        $places = collect($request->places);

        $maxLength = $places->max('length') / 100;
        $maxWidth = $places->max('width') / 100;
        $maxHeight = $places->max('height') / 100;
        $maxDimension =  $maxDimension->max() / 100;

        $totalVolume = round(($maxLength * $maxWidth * $maxHeight), 2);

        return [
            'maxWeight' => (float) $totalWeight, // максимальный вес груза
            'maxVolume' => (float) $totalVolume, // максимальный объём груза
            'maxWeightPerPlace' => (float) $maxWeightPerPlace, // максимальный вес грузоместа
            'maxDimension' => (float) $maxDimension, // максимальный габарит грузоместа
        ];
    }

    // Добавить проверку на наложенный платёж

    private function checkCargo(array $places, $type)
    {
        switch ($type) {
            case 'auto':
                $maxWeight = 20000;
                $maxLength = 13.4;
                $maxWidth = 2.42;
                $maxHeight = 2.45;
                break;
            case 'avia':
                $maxWeight = 80;
                $maxLength = 2;
                $maxWidth = 1;
                $maxHeight = 0.8;
                break;
        }

        foreach ($places as $place) {

            $transmittedWeight = $place['weight'];
            $transmittedLength = isset($place['length']) ? $place['length'] / 100 : 0;
            $transmittedWidth = isset($place['width']) ? $place['width'] / 100 : 0;
            $transmittedHeight = isset($place['height']) ? $place['height'] / 100 : 0;

            if (
                $transmittedWeight > $maxWeight
                || $transmittedLength > $maxLength
                || $transmittedWidth > $maxWidth
                || $transmittedHeight > $maxHeight
            ) {
                throw new Exception("Параметры груза превышают допустимые габариты", 500);
            }
        }
    }
}
