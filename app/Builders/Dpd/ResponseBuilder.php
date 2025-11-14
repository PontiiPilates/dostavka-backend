<?php

declare(strict_types=1);

namespace App\Builders\Dpd;

use App\DTO\CalculationResultDto;
use App\Enums\CompanyType;
use App\Enums\Dpd\DpdUrlType;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;


class ResponseBuilder
{
    private string $url;
    private string $company;

    public function __construct()
    {
        $this->url = config('companies.dpd.url');
        $this->company = CompanyType::DPD->value;
    }

    /**
     * Обеспечивает сборку требуемой структуры ответа.
     * 
     * @param array $responses
     * @return array
     */
    public function build($responses): array
    {
        $result = CalculationResultDto::filler($this->company);

        foreach ($responses as $type => $response) {

            foreach ($response->return as $tariff) {

                // где-то здесь однажды должна появиться ошибка, ее нужно поймать и обработать

                if (isset($tariff->message)) {

                    $errorId = Str::random(10);

                    Log::channel('tk')->error(
                        sprintf('Ошибка %s при обработке ответа: %s%s %s %s', $errorId, $this->url, DpdUrlType::Calculator->value, __FILE__, __LINE__),
                        [$response->error]
                    );

                    continue;
                }

                $result['data']['success'][$type][] = CalculationResultDto::tariff(
                    $tariff->serviceName,
                    $tariff->cost,
                    $tariff->days,
                    $tariff->days,
                );
            }
        }

        // если нет успешных
        if (empty($result['data']['success'])) {
            throw new Exception(trans('messages.response.not_results'), 200);
        } else {
            return $result;
        }
    }
}
