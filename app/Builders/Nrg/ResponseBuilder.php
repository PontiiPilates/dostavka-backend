<?php

declare(strict_types=1);

namespace App\Builders\Nrg;

use App\DTO\CalculationResultDto;
use App\Enums\CompanyType;
use App\Enums\DeliveryType;
use App\Enums\Nrg\NrgUrlType;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ResponseBuilder
{
    private string $url;
    private string $company;

    private $daysFrom;
    private $daysTo;

    public function __construct()
    {
        $this->url = config('companies.nrg.url') . NrgUrlType::Price->value;
        $this->company = CompanyType::Nrg->value;
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

        foreach ($responses as $type => $response) {

            $response = $response->object();

            // реакция на наличие ошибки запроса
            if (isset($response->code) && isset($response->message)) {
                $errorId = Str::random(10);

                Log::channel('tk')->error(
                    sprintf('Ошибка %s при обработке ответа: %s %s %s', $errorId, $this->url, __FILE__, __LINE__),
                    [$response->extraInfo]
                );

                continue;
            }

            foreach ($response->transfer as $tariff) {

                // реакция на наличие ошибки тарифа
                if (isset($tariff->hasError) && $tariff->hasError === true) {
                    $errorId = Str::random(10);

                    Log::channel('tk')->error(
                        sprintf('Ошибка %s при обработке ответа: %s %s %s', $errorId, $this->url, __FILE__, __LINE__),
                        [$tariff->errorMessage]
                    );

                    continue;
                }

                // стоимость по тарифу
                $cost = $tariff->price;

                // стоимость с учётом негабарита
                if ($tariff->oversize != null) $cost += $tariff->oversize->price;

                // стоимость с учетом способов доставки
                switch ($type) {
                    case DeliveryType::Sd->value:
                        $cost += $response->request->price;
                        break;
                    case DeliveryType::Ds->value:
                        $cost += $response->delivery->price;
                        break;
                    case DeliveryType::Dd->value:
                        $cost += $response->delivery->price + $response->request->price;
                        break;
                }

                // стоимость с учётом надбавки за объявленную ценность
                $cost += $response->priceInsurance;

                $this->parseDate($tariff->interval);

                $result['data']['success'][$type][] = CalculationResultDto::tariff(
                    $tariff->type,
                    $cost,
                    $this->daysFrom,
                    $this->daysTo ?? null,
                );
            }
        }

        return $result;
    }

    private function parseDate($interval)
    {
        $interval = Str::remove(' дней', $interval);
        $interval = explode('-', $interval);

        $this->daysFrom = $interval[0];
        $this->daysTo = $interval[1];
    }
}
