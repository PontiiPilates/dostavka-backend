<?php

declare(strict_types=1);

namespace App\Builders\Pek;

use App\Enums\Pek\PekTariffType;
use App\Enums\Pek\PekUrlType;
use Exception;
use Illuminate\Support\Facades\Log;

class ResponseBuilder
{
    private string $url;

    public function __construct()
    {
        $this->url = config('companies.pek.url');
    }

    /**
     * Обеспечивает сборку требуемой структуры ответа.
     * 
     * @param array $responses
     * @return array
     */
    public function build(array $responses): array
    {
        $tariffs = [
            PekTariffType::AviaExpress->value => PekTariffType::AviaExpress->label(),
            PekTariffType::Avia->value => PekTariffType::Avia->label(),
            PekTariffType::Auto->value => PekTariffType::Auto->label(),
            PekTariffType::AutoExpress->value => PekTariffType::AutoExpress->label(),
            PekTariffType::AutoDts->value => PekTariffType::AutoDts->label(),
            PekTariffType::AutoEasyWay->value => PekTariffType::AutoEasyWay->label(),
        ];

        $data = [];

        foreach ($responses as $type => $response) {
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

                $data[$type][] = [
                    "tariff" => $tariffs[$tariff->type],
                    "cost" => $tariff->costTotal ?? null,
                    "days" => [
                        "from" => null,
                        "to" => $tariff->estDeliveryTime ?? null,
                    ]
                ];
            }
        }

        return $data;
    }

    private function checkTariffError($tariff)
    {
        if ($tariff->hasError === true) {
            Log::channel('tk')->error('Ошибка при обработке ответа: ' . $this->url . PekUrlType::Calculate->value, [$tariff->errorMessage]);
            throw new Exception("Ошибка при обработке ответа. Тариф содержит ошибку и будет исключён из итоговой сводки.", 500);
        }
    }

    private function checkResponseError($response)
    {
        if (isset($response->error)) {
            Log::channel('tk')->error('Ошибка при обработке ответа: ' . $this->url . PekUrlType::Calculate->value, [$response->error->fields]);
            throw new Exception('Ошибка при обработке ответа. Ответ содержит ошибку и будет исключён из итоговой сводки', 500);
        }
    }
}
