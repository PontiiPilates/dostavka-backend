<?php

declare(strict_types=1);

namespace App\Builders\Dellin;

use Exception;

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

            $multiKey = explode(':', $key);
            $type = $multiKey[0];
            $tariff = $multiKey[1];

            try {
                $this->isError($response);
            } catch (\Throwable $th) {
                continue;
            }

            $data[$type][] = [
                "tariff" => $tariff,
                "cost" => $response->data->price ?? null,
                "days" => [
                    "from" => null,
                    "to" => $response->data->orderDates->derivalToAddressMax
                        ?? $response->data->orderDates->arrivalToAirportMax
                        ?? null,
                    "date" => $response->data->orderDates->arrivalToOspReceiver // дата прибытия на терминал-получатель
                        ?? $response->data->orderDates->derivalFromOspReceiver // дата отправки с терминала-получателя
                        ?? $response->data->orderDates->arrivalToAirport // дата прибытия на терминал получателя / в аэропорт
                        ?? null
                ]
            ];
        }

        return $data;
    }

    private function isError($response)
    {
        if (isset($response->errors)) {
            throw new Exception("Ошибка при выполнении запроса: недопустимое значение параметров либо услуга не может быть оказана", 500);
        }
    }
}
