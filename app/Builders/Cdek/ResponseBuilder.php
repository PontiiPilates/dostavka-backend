<?php

declare(strict_types=1);

namespace App\Builders\Cdek;

use App\Enums\Cdek\CdekDeliveryType;
use App\Enums\Cdek\CdekUrlType;
use App\Enums\CompanyType;
use App\Enums\DeliveryType;
use Exception;
use Illuminate\Support\Facades\Log;

class ResponseBuilder
{
    private string $url;

    public function __construct()
    {
        $this->url = config('companies.boxberry.url');
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
            'company' => CompanyType::Cdek->value,
            'types' => [],
        ];

        foreach ($responses as $response) {
            $response = $response->object();

            // при наличии ошибки в ответе
            try {
                $this->checkResponseError($response);
            } catch (\Throwable $th) {
                continue;
            }

            $types = [
                CdekDeliveryType::Dd->value => DeliveryType::Dd->value,
                CdekDeliveryType::Ds->value => DeliveryType::Ds->value,
                CdekDeliveryType::Sd->value => DeliveryType::Sd->value,
                CdekDeliveryType::Ss->value => DeliveryType::Ss->value,
                CdekDeliveryType::Tt->value => DeliveryType::Tt->value,
                CdekDeliveryType::Dp->value => DeliveryType::Dp->value,
                CdekDeliveryType::Sp->value => DeliveryType::Sp->value,
                CdekDeliveryType::Pd->value => DeliveryType::Pd->value,
                CdekDeliveryType::Ps->value => DeliveryType::Ps->value,
                CdekDeliveryType::Pp->value => DeliveryType::Pp->value,
            ];

            foreach ($response->tariff_codes as $tariff) {
                $type = $types[$tariff->delivery_mode];

                $data['types'][$type][] = [
                    "tariff" => $tariff->tariff_name,
                    "cost" => $tariff->delivery_sum,
                    "days" => [
                        "from" => $tariff->calendar_min,
                        "to" => $tariff->calendar_max,
                    ]
                ];
            }
        }

        return $data;
    }

    /**
     * Проверка наличия ошибок в ответе.
     */
    private function checkResponseError($response): void
    {
        if (empty($response->tariff_codes)) {
            $message = 'Ошибка при обработке ответа: (обнаружена при отсутствии тарифов для груза с такими параметрами)' . $this->url . ': ' . CdekUrlType::TariffList->value . ': ' . __FILE__;
            Log::channel('tk')->error($message);
            throw new Exception($message, 500);
        }

        if (isset($response->requests[0]->errors)) {
            $message = 'Ошибка при обработке ответа: ' . $this->url . ': ' . CdekUrlType::TariffList->value . ': ' . __FILE__;
            Log::channel('tk')->error($message, [$response->requests[0]->errors]);
            throw new Exception($message, 500);
        }
    }
}
