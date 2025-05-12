<?php

declare(strict_types=1);

namespace App\Builders\Vozovoz;

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

            $data[$key][] = [
                "tariff" => null,
                "cost" => $response->response->price,
                "days" => [
                    "from" => $response->response->deliveryTime->from,
                    "to" => $response->response->deliveryTime->to,
                ]
            ];
        }

        return $data;
    }
}
