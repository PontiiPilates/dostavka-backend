<?php

declare(strict_types=1);

namespace App\Builders\Dpd;

use App\Enums\CompanyType;

class ResponseBuilder
{
    /**
     * Обеспечивает сборку требуемой структуры ответа.
     * 
     * @param array $responses
     * @return array
     */
    public function build($responses): array
    {
        $data = [
            'company' => CompanyType::DPD->value,
            'types' => [],
        ];

        foreach ($responses as $type => $response) {

            foreach ($response->return as $tariff) {

                $data['types'][$type][] = [
                    "tariff" => $tariff->serviceName,
                    "cost" => $tariff->cost,
                    "days" => [
                        "from" => $tariff->days,
                        "to" => $tariff->days,
                    ]
                ];
            }
        }

        return $data;
    }
}
