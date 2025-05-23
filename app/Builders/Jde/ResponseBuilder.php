<?php

declare(strict_types=1);

namespace App\Builders\Jde;

use App\Enums\Jde\JdeTariffType;
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

            $multiKey = explode(':', $key);
            $type = $multiKey[0];
            $tariff = $multiKey[1];

            try {
                $this->isError($response);
            } catch (\Throwable $th) {
                continue;
            }

            $tariffs = [
                JdeTariffType::Combined->value => JdeTariffType::Combined->label(),
                // JdeTariffType::Express->value =>  JdeTariffType::Express->label(), // не обслуживается
                // JdeTariffType::Individual->value => eTariffType::Individual->label(), // не обслуживается
                // JdeTariffType::Internet->value => JdeTariffType::Internet->label(), // не обслуживается
                // JdeTariffType::Courier->value =>  JdeTariffType::Courier->label(), // не обслуживается
            ];

            $data[$type][] = [
                "tariff" => $tariffs[$tariff],
                "cost" => $response->price,
                "days" => [
                    "from" => $response->mindays,
                    "to" => $response->maxdays,
                    "date" => '',
                ]
            ];
        }

        return $data;
    }

    private function isError($response)
    {
        if (isset($response->error)) {
            Log::channel('tk')->error('Ошибка при выполнении запроса', [$response->error]);
            throw new Exception('Ошибка при выполнении запроса, смотри лог', 500);
        }

        foreach ($response->services as $service) {
            if (isset($service->error)) {
                Log::channel('tk')->error('Ошибка при выполнении запроса', [$response->services[0]->error]);
                throw new Exception('Ошибка при выполнении запроса, смотри лог', 500);
            }
        }
    }
}
