<?php

declare(strict_types=1);

namespace App\Builders\Pek;

use App\Enums\CompanyType;
use App\Enums\Pek\PekTariffType;
use App\Enums\Pek\PekUrlType;
use Exception;
use Illuminate\Support\Facades\Log;

class ResponseBuilder
{
    private string $url;

    private string|null $daysFrom = null;
    private string|null $daysTo = null;

    public function __construct()
    {
        $this->url = config('companies.pek.url') . PekUrlType::Calculate->value;
    }

    /**
     * Обеспечивает сборку требуемой структуры ответа.
     * 
     * @param array $responses
     * @return array
     */
    public function build(array $responses): array
    {
        $data = [
            'company' => CompanyType::Pek->value,
            'types' => [],
        ];

        $tariffs = [
            PekTariffType::AviaExpress->value => PekTariffType::AviaExpress->label(),
            PekTariffType::Auto->value => PekTariffType::Auto->label(),
            // PekTariffType::AutoExpress->value => PekTariffType::AutoExpress->label(), // не обслуживается
            PekTariffType::AutoDts->value => PekTariffType::AutoDts->label(),
            PekTariffType::AutoEasyWay->value => PekTariffType::AutoEasyWay->label(),
        ];

        foreach ($responses as $deliveryType => $response) {
            $response = $response->object();

            // реакция на наличие ошибок запроса
            try {
                $this->checkResponseError($response);
            } catch (\Throwable $th) {
                continue;
            }

            foreach ($response->transfers as $tariff) {

                // реакция на наличие ошибок при расчёте тарифа
                try {
                    $this->checkTariffError($tariff);
                } catch (\Throwable $th) {
                    continue;
                }

                // тк использует разные структуры для сроков доставки и праметров тарифа
                // здесь происходит мэтч этих структур с целью подготовки данных о сроках доставки
                foreach ($response->commonTerms as $timeItem) {
                    if ($timeItem->type === $tariff->type) {
                        $this->daysPrepare($deliveryType, $timeItem);
                    }
                }

                $data['types'][$deliveryType][] = [
                    "tariff" => $tariffs[$tariff->type],
                    "cost" => $tariff->costTotal ?? null,
                    "days" => [
                        "from" => $this->daysFrom,
                        "to" => $this->daysTo,
                    ]
                ];
            }
        }

        return $data;
    }


    private function checkTariffError($tariff)
    {
        if ($tariff->hasError === true) {
            $message = 'Ошибка при обработке ответа: ' . $this->url;
            Log::channel('tk')->error($message, [$tariff->errorMessage]);
            throw new Exception($message, 500);
        }
    }

    private function checkResponseError($response)
    {
        if (isset($response->error)) {
            $message = 'Ошибка при обработке ответа: ' . $this->url;
            Log::channel('tk')->error($message, [$response->error->fields]);
            throw new Exception($message, 500);
        }
    }

    /**
     * Устанавливает значения сроков доставки для соответствующих свойств.
     * 
     * @param string $deliveryType
     * @param object $timeItem
     * @return void
     */
    private function daysPrepare(string $deliveryType, object $timeItem): void
    {
        switch ($deliveryType) {
            case 'ss':
                $this->daysFrom = $timeItem->transporting[0] ?? null;
                $this->daysTo = $timeItem->transporting[1] ?? null;
                break;
            case 'sd':
                $this->daysFrom = $timeItem->transportingWithDelivery[0] ?? null;
                $this->daysTo = $timeItem->transportingWithDelivery[1] ?? null;
                break;
            case 'ds':
                $this->daysFrom = $timeItem->transportingWithPickup ?? null;
                $this->daysTo = $timeItem->transportingWithPickup ?? null;
                break;
            case 'dd':
                $this->daysFrom = $timeItem->transportingWithDeliveryWithPickup ?? null;
                $this->daysTo = $timeItem->transportingWithDeliveryWithPickup ?? null;
                break;
        }
    }
}
