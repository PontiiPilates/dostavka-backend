<?php

declare(strict_types=1);

namespace App\Builders\Jde;

use App\Enums\CompanyType;
use App\Enums\Jde\JdeTariffType;
use App\Enums\Jde\JdeUrlType;
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
        $data = [
            'company' => CompanyType::Jde->value,
            'types' => [],
        ];

        foreach ($responses as $key => $response) {
            $response = $response->object();

            $multiKey = explode(':', $key);
            $type = $multiKey[0];
            $tariff = $multiKey[1];

            // при наличии ошибки в ответе
            try {
                $this->checkResponseError($response);
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

            $data['types'][$type][] = [
                "tariff" => $tariffs[$tariff],
                "cost" => $response->price,
                "days" => [
                    "from" => $response->mindays,
                    "to" => $response->maxdays,
                ]
            ];
        }

        return $data;
    }

    private function checkResponseError($response)
    {
        $url = config('companies.jde.url') . JdeUrlType::Calculator->value;

        if (isset($response->error)) {
            $message = 'Ошибка при обработке ответа: ' . $url;
            Log::channel('tk')->error($message, [$response->error]);
            throw new Exception($message, 500);
        }

        foreach ($response->services as $service) {
            if (isset($service->error)) {
                $message = 'Ошибка при обработке ответа: ' . $url;
                Log::channel('tk')->error($message, [$response->services[0]->error]);
                throw new Exception($message, 500);
            }
        }
    }
}
