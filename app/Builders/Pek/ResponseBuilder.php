<?php

declare(strict_types=1);

namespace App\Builders\Pek;

use App\DTO\CalculationResultDto;
use App\Enums\CompanyType;
use App\Enums\Pek\PekTariffType;
use App\Enums\Pek\PekUrlType;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ResponseBuilder
{
    private string $url;
    private string $company;

    private string|null $daysFrom = null;
    private string|null $daysTo = null;

    public function __construct()
    {
        $this->url = config('companies.pek.url') . PekUrlType::Calculate->value;
        $this->company = CompanyType::Pek->value;
    }

    /**
     * Обеспечивает сборку требуемой структуры ответа.
     * 
     * @param array $responses
     * @return array
     */
    public function build(array $responses): array
    {
        $result = CalculationResultDto::filler($this->company);

        $tariffs = [
            PekTariffType::AviaExpress->value => PekTariffType::AviaExpress->label(),
            PekTariffType::Auto->value => PekTariffType::Auto->label(),
            // PekTariffType::AutoExpress->value => PekTariffType::AutoExpress->label(), // не обслуживается
            PekTariffType::AutoDts->value => PekTariffType::AutoDts->label(),
            PekTariffType::AutoEasyWay->value => PekTariffType::AutoEasyWay->label(),
        ];

        foreach ($responses as $deliveryType => $response) {
            $response = $response->object();

            foreach ($response->transfers as $tariff) {

                // данная компания способна вернуть ошибку по каждому тарифу
                // в негативном случае пользователь может получить список, состоящий из одних ошибок
                // если нет ни одного результата, то лучше возвращать одну ошибку
                // остальное записывать в лог
                if ($tariff->hasError === true) {

                    $errorId = Str::random(10);

                    Log::channel('tk')->error(
                        sprintf('Ошибка %s при обработке ответа в тарифе %s: %s%s %s %s', $errorId, $tariffs[$tariff->type], $this->url, PekUrlType::Calculate->value, __FILE__, __LINE__),
                        [$tariff->errorMessage]
                    );

                    continue;
                }

                // тк использует разные структуры для сроков доставки и праметров тарифа
                // здесь происходит мэтч этих структур с целью подготовки данных о сроках доставки
                foreach ($response->commonTerms as $timeItem) {
                    if ($timeItem->type === $tariff->type) {
                        $this->daysPrepare($deliveryType, $timeItem);
                    }
                }

                $result['data']['success'][$deliveryType][] = CalculationResultDto::tariff(
                    $tariffs[$tariff->type],
                    $tariff->costTotal ?? null,
                    $this->daysFrom,
                    $this->daysTo,
                );
            }
        }

        // если нет успешных
        if (empty($result['data']['success'])) {
            throw new Exception(trans('messages.response.not_results'), 200);
        } else {
            return $result;
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
