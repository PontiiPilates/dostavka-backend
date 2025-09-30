<?php

declare(strict_types=1);

namespace App\Builders\Baikal;

use App\DTO\CalculationResultDto;
use App\Enums\Baikal\BaikalUrlType;
use App\Enums\CompanyType;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ResponseBuilder
{
    private string $url;
    private string $company;

    public function __construct()
    {
        $this->url = config('companies.baikal.url');
        $this->company = CompanyType::Baikal->value;
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

        foreach ($responses as $type => $response) {
            $response = $response->object();

            // отладка
            if (env('SHOW_Q')) {
                dump($response);
            }

            if (isset($response->error)) {

                $errorId = Str::random(10);

                Log::channel('tk')->error(
                    sprintf('Ошибка %s при обработке ответа: %s%s %s %s', $errorId, $this->url, BaikalUrlType::Calculator->value, __FILE__, __LINE__),
                    [$response->error]
                );

                continue;
            }

            $result['data']['success'][$type] = CalculationResultDto::tariff(
                'Автоперевозка',
                $response->total,
                $response->transit->int,
                $response->transit->int
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
