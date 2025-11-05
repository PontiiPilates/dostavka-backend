<?php

declare(strict_types=1);

namespace App\Builders\Pochta;

use App\DTO\CalculationResultDto;
use App\Enums\CompanyType;
use App\Enums\Pochta\PochtaUrlType;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;


class ResponseBuilder
{
    private string $url;
    private string $company;

    public function __construct()
    {
        $this->url = config('companies.pochta.url') . PochtaUrlType::Calculate->value;
        $this->company = CompanyType::Pochta->value;
    }

    /**
     * Обеспечивает сборку требуемой структуры ответа.
     * 
     * @param array $responses
     * @return array
     */
    public function build(array $responses): array
    {
        $result = CalculationResultDto::filler($this->company);

        foreach ($responses as $key => $response) {
            $response = $response->object();

            $multiKey = explode(':', $key);
            $deliveryType = $multiKey[0];
            $tariff = $multiKey[1];

            if (isset($response->errors)) {
                $errorId = Str::random(10);

                Log::channel('tk')->error(
                    sprintf('Ошибка %s при обработке ответа: %s %s %s', $errorId, $this->url, __FILE__, __LINE__),
                    [$response->errors]
                );

                continue;
            }

            $result['data']['success'][$deliveryType][] = CalculationResultDto::tariff(
                $response->name,
                isset($response->paynds) ? $response->paynds / 100 : null,
                $response->delivery->min ?? null,
                $response->delivery->max ?? null,
            );
        }

        // если нет успешных
        if (empty($result['data']['success'])) {
            throw new Exception(trans('messages.response.not_results'), 200);
        } else {
            return $result;
        }
    }
}
