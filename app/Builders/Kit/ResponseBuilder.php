<?php

declare(strict_types=1);

namespace App\Builders\Kit;

use App\Builders\BaseBuilder;
use App\DTO\CalculationResultDto;
use App\Enums\CompanyType;
use App\Enums\Kit\KitUrlType;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ResponseBuilder extends BaseBuilder
{
    private string $url;

    private string $company;

    public function __construct()
    {
        $this->url = config('companies.kit.url') . KitUrlType::Calculate->value;
        $this->company = CompanyType::Kit->value;
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

            // при наличии ошибки в ответе
            if (isset($response->validate) || gettype($response) == 'string') {
                $errorId = Str::random(10);

                Log::channel('tk')->error(
                    sprintf('Ошибка %s при обработке ответа: %s %s %s', $errorId, $this->url, __FILE__, __LINE__),
                    [$response->errors]
                );

                continue;
            }

            foreach ($response[0] as $item) {

                $result['data']['success'][$type][] = CalculationResultDto::tariff(
                    $item->name,
                    $item->cost,
                    $item->time,
                    $item->time,
                );
            }
        }

        return $result;
    }
}
