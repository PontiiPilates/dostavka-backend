<?php

declare(strict_types=1);

namespace App\Builders\Nrg;

use App\Enums\CompanyType;
use App\Enums\DeliveryType;
use App\Enums\Nrg\NrgUrlType;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ResponseBuilder
{
    private string $url;

    private $daysFrom;
    private $daysTo;

    public function __construct()
    {
        $this->url = config('companies.nrg.url') . NrgUrlType::Price->value;
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
            'company' => CompanyType::Nrg->value,
            'types' => [],
        ];

        foreach ($responses as $type => $response) {

            $response = $response->object();

            // реакция на наличие ошибки запроса
            try {
                $this->checkResponseError($response);
            } catch (\Throwable $th) {
                continue;
            }

            foreach ($response->transfer as $tariff) {

                // реакция на наличие ошибки тарифа
                try {
                    $this->checkTariffError($tariff);
                } catch (\Throwable $th) {
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

                $data['types'][$type][] = [
                    "tariff" => $tariff->type,
                    "cost" => $cost,
                    "days" => [
                        "from" => $this->daysFrom,
                        "to" => $this->daysTo ?? null,
                    ]
                ];
            }
        }

        return $data;
    }

    private function checkTariffError($tariff)
    {
        if (isset($tariff->hasError) && $tariff->hasError === true) {
            Log::channel('tk')->error('Ошибка при обработке ответа: ' . $this->url, [$tariff->errorMessage]);
            throw new Exception("Ошибка при обработке ответа. Тариф содержит ошибку и будет исключён из итоговой сводки.", 500);
        }
    }

    private function checkResponseError($response)
    {
        if (isset($response->code) && isset($response->message)) {
            Log::channel('tk')->error('Ошибка при обработке ответа: ' . $this->url, [$response->extraInfo]);
            throw new Exception('Ошибка при обработке ответа. Ответ содержит ошибку и будет исключён из итоговой сводки', 500);
        }
    }

    private function parseDate($interval)
    {
        $interval = Str::remove(' дней', $interval);
        $interval = explode('-', $interval);

        $this->daysFrom = $interval[0];
        $this->daysTo = $interval[1];
    }
}
