<?php

declare(strict_types=1);

namespace App\Builders\Boxberry;

use App\Builders\BaseBuilder;
use App\Enums\Boxberry\BoxberryUrlType;
use App\Enums\CompanyType;
use Exception;
use Illuminate\Support\Facades\Log;

class ResponseBuilder extends BaseBuilder
{
    private string $url;

    public function __construct()
    {
        $this->url = config('companies.boxberry.url');
    }

    /**
     * Обеспечивает сборку требуемой структуры ответа.
     * 
     * @param array $responses
     * @return array
     */
    public function build(array $responses): array
    {
        $data = [
            'company' => CompanyType::Boxberry->value,
            'types' => [],
        ];

        foreach ($responses as $type => $response) {
            $response = $response->object();

            // при наличии ошибки в ответе
            try {
                $this->checkResponseError($response);
            } catch (\Throwable $th) {
                continue;
            }

            foreach ($response->result->DeliveryCosts as $item) {
                $data['types'][$type][] = [
                    "tariff" => 'Посылка',
                    "cost" => $item->TotalPrice,
                    "days" => [
                        "from" => $item->DeliveryPeriod,
                        "to" => $item->DeliveryPeriod,
                    ]
                ];
            }
        }

        return $data;
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
