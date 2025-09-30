<?php

declare(strict_types=1);

namespace App\Builders\Boxberry;

use App\Builders\BaseBuilder;
use App\DTO\CalculationResultDto;
use App\Enums\Boxberry\BoxberryUrlType;
use App\Enums\CompanyType;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ResponseBuilder extends BaseBuilder
{
    private string $url;

    private string $company;

    public function __construct()
    {
        $this->url = config('companies.boxberry.url');
        $this->company = CompanyType::Boxberry->value;
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

        foreach ($responses as $deliveryType => $response) {
            $response = $response->object();

            // отладка
            if (env('SHOW_Q')) {
                dump($response);
            }

            if ($response->error != false) {

                $errorId = Str::random(10);

                Log::channel('tk')->error(
                    sprintf('Ошибка %s при обработке ответа: %s%s %s %s', $errorId, $this->url, BoxberryUrlType::DeliveryCalculation->value, __FILE__, __LINE__),
                    [$response->error]
                );

                continue;
            }

            foreach ($response->result->DeliveryCosts as $item) {

                $result['data']['success'][$deliveryType][] = CalculationResultDto::tariff(
                    'Посылка',
                    $item->TotalPrice,
                    $item->DeliveryPeriod,
                    $item->DeliveryPeriod,
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

    /**
     * Проверка наличия ошибки в ответе: выбрасывает исключение и логирует данные при обнаружении ошибки в ответе.
     * 
     * @var $response
     * @return void
     */
    private function checkResponseError($response): void
    {
        if ($response->error != false) {
            $message = 'Ошибка при обработке ответа: ' . $this->url . ': ' . BoxberryUrlType::DeliveryCalculation->value . ': ' . __FILE__;
            Log::channel('tk')->error($message,  [$response->message]);
            throw new Exception($message, 500);
        }
    }
}
