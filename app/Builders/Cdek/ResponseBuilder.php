<?php

declare(strict_types=1);

namespace App\Builders\Cdek;

use App\Enums\Cdek\CdekDeliveryType;
use App\Enums\DeliveryType;
use Exception;
use Illuminate\Support\Facades\Log;

class ResponseBuilder
{
    /**
     * Обеспечивает сборку требуемой структуры ответа.
     * 
     * @param array $responses
     * @return array
     */
    public function build(array $responses): array
    {
        $data = [];
        foreach ($responses as $key => $response) {

            $response = $response->object();

            // если ответ ничего не содержит, то происходит переход к обработке следующего ответа
            try {
                $this->checkEmpty($response);
            } catch (\Throwable $th) {
                continue;
            }

            // если ответ содержит ошибку, то происходит переход к обработке следующего ответа
            try {
                $this->checkError($response);
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

                $data[$type][] = [
                    'tariff' => $tariff->tariff_name,
                    'cost' => $tariff->delivery_sum,
                    'days' => [
                        'from' => $tariff->calendar_min,
                        'to' => $tariff->calendar_max,
                        'date' => now()->addDays($tariff->calendar_max)->isoFormat('YYYY-MM-DD'),
                    ]
                ];
            }
        }

        return $data;
    }

    /**
     * Проверка наличия ошибок в ответе.
     */
    private function checkError($response): void
    {
        if (isset($response->requests[0]->errors)) {
            Log::channel('tk')->error('Ошибка при обработке ответа', [$response->requests[0]->errors]);
            throw new Exception('Ошибка при обработке ответа, смотри лог', 500);
        }
    }

    private function checkEmpty($response)
    {
        if (empty($response->tariff_codes)) {
            Log::channel('tk')->error('Ошибка при обработке ответа. Нет тарифов для груза с такими параметрами.');
            throw new Exception('Ошибка при обработке ответа. Нет тарифов для груза с такими параметрами.', 500);
        }
    }
}
