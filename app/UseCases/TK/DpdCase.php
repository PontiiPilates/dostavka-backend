<?php

declare(strict_types=1);

namespace App\UseCases\TK;

use App\Enums\CompanyType;
use App\Enums\DPD\DpdUrlType;
use Illuminate\Http\Request;

class DpdCase extends BaseCase
{
    private string $uri;
    private string $clientNumber;
    private string $clientKey;

    public function __construct()
    {
        $this->uri = config('companies.dpd.uri');
        $this->clientNumber = config('companies.dpd.client_number');
        $this->clientKey = config('companies.dpd.client_key');
    }

    public function handle(Request $request)
    {
        // todo: подготовка данных, возможно лучше обернуть в метод prepare, а переменные сделать свойствами
        try {
            $from = $this->moreInfo($request->from);
            $to = $this->moreInfo($request->to);
            $regimes = $request->regimes;
            $places = $request->places;
            $sumoc = $request->sumoc;
            $sumnp = $request->sumnp;
            $shipmentDate = $request->shipment_date;
            $isInternational = $this->isInternational($from->country_code, $to->country_code);
        } catch (\Throwable $th) {
            dd($th->getMessage());
        }

        // режим является главной конструкцией, которая разделяет логику
        foreach ($regimes as $regime) {

            $regimeSwitchers = $this->regimeSwitchers($regime);

            // todo: переписать в метод buildDto и собирать в нем DTO
            $dto['request'] = [
                'declaredValue' => $sumoc, // объявленная ценность (итоговая)
                'parcel' => $places,
                'pickup' => [
                    'cityId' => $from->city_id, // откуда
                    'cityName' => $from->city_name, // откуда
                ],
                'delivery' => [
                    'cityId' => $to->city_id, // куда
                    'cityName' => $to->city_name, // куда
                ],
                'pickupDate' => $shipmentDate, // дата сдачи груза
                'selfPickup' => $regimeSwitchers['selfPickup'],
                'selfDelivery' => $regimeSwitchers['selfDelivery'],
                'auth' => [
                    'clientNumber' => $this->clientNumber,
                    'clientKey' => $this->clientKey,
                ]
            ];

            $apiResponses[$regime] = $this->calculate($dto);
        }

        return $this->responseBuilder($apiResponses);
    }

    /**
     * Возвращает переключатели в соответствии с режимом доставки.
     * 
     * selfPickup: true - отправитель довозит до терминала / false - курьер забирает у отправителя
     * selfDelivery: true - получатель забирает сам / false - курьер доставляет получателю
     */
    private function regimeSwitchers($selectedRegime): array
    {
        switch ($selectedRegime) {
            case 'ss': // (склад-склад)
                return [
                    'selfPickup' => true,
                    'selfDelivery' => true,
                ];
            case 'sd': // (склад-дверь)
                return [
                    'selfPickup' => true,
                    'selfDelivery' => false,
                ];
            case 'ds': // (дверь-склад)
                return [
                    'selfPickup' => false,
                    'selfDelivery' => true,
                ];
            default: // dd (дверь-дверь)
                return [
                    'selfPickup' => false,
                    'selfDelivery' => false,
                ];
        }
    }

    /**
     * Расчёт доставки.
     */
    private function calculate($dto)
    {
        return $this->sendSoap($uri = $this->uri . DpdUrlType::Calculator->value, $dto);
    }

    /**
     * Формирует структуру ответа кейса.
     */
    private function responseBuilder($apiResponses)
    {
        $response = [];

        foreach ($apiResponses as $key => $value) {

            // если обратиться свойству невозможно, то ответ завершился ошибкой
            // фронтенду знать ее не обязательно, поэтому пропуск
            // (ошибка сохраняется в лог)
            if (!$value?->return) {
                $response[$key][CompanyType::DPD->value][] = null;
                continue;
            }

            foreach ($value->return as $item) {
                $response[$key][CompanyType::DPD->value][] = [
                    'tariff' => $item->serviceName,
                    'cost' => $item->cost,
                    'days' => $item->days,
                ];
            }
        }

        return $response;
    }
}
