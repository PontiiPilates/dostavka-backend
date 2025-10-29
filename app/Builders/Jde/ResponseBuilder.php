<?php

declare(strict_types=1);

namespace App\Builders\Jde;

use App\DTO\CalculationResultDto;
use App\Enums\CompanyType;
use App\Enums\Jde\JdeTariffType;
use App\Enums\Jde\JdeUrlType;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ResponseBuilder
{
    private string $url;
    private string $company;

    public function __construct()
    {
        $this->url = config('companies.jde.url') . JdeUrlType::Calculator->value;
        $this->company = CompanyType::Jde->value;
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
            $type = $multiKey[0];
            $tariff = $multiKey[1];

            // обработка ошибок
            $this->checkResponseError($response);

            $tariffs = [
                JdeTariffType::Combined->value => JdeTariffType::Combined->label(),
                // JdeTariffType::Express->value =>  JdeTariffType::Express->label(), // не обслуживается
                // JdeTariffType::Individual->value => eTariffType::Individual->label(), // не обслуживается
                // JdeTariffType::Internet->value => JdeTariffType::Internet->label(), // не обслуживается
                // JdeTariffType::Courier->value =>  JdeTariffType::Courier->label(), // не обслуживается
            ];

            $result['data']['success'][$type] = CalculationResultDto::tariff(
                $tariffs[$tariff],
                $response->price,
                $response->mindays,
                $response->maxdays,
            );
        }

        // если нет успешных
        if (empty($result['data']['success'])) {
            throw new Exception(trans('messages.response.not_results'), 200);
        } else {
            return $result;
        }
    }

    private function checkResponseError($response)
    {
        if (isset($response->error)) {
            $errorId = Str::random(10);
            Log::channel('tk')->error(
                sprintf('Ошибка %s при обработке ответа: %s%s %s %s', $errorId, $this->company, $this->url, __FILE__, __LINE__),
                [$response->error]
            );
        }

        foreach ($response->services as $service) {
            if (isset($service->error)) {
                $errorId = Str::random(10);
                Log::channel('tk')->error(
                    sprintf('Ошибка %s при обработке ответа: %s%s %s %s', $errorId, $this->company, $this->url, __FILE__, __LINE__),
                    [$response->services[0]->error]
                );
            }
        }
    }
}
