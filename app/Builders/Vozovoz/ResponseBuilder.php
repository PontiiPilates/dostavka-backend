<?php

declare(strict_types=1);

namespace App\Builders\Vozovoz;

use App\Enums\CompanyType;
use Exception;
use Illuminate\Support\Facades\Log;

class ResponseBuilder
{
    private string $url;

    public function __construct()
    {
        $this->url = config('companies.vozovoz.url');
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
            'company' => CompanyType::Vozovoz->value,
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

            $data['types'][$type][] = [
                "tariff" => null,
                "cost" => $response->response->price,
                "days" => [
                    "from" => $response->response->deliveryTime->from,
                    "to" => $response->response->deliveryTime->to,
                ]
            ];
        }

        return $data;
    }

    /**
     * Проверка наличия ошибки в ответе: выбрасывает исключение и логирует данные при обнаружении ошибки в ответе.
     * 
     * @var object $response
     * @return void
     */
    private function checkResponseError(object $response): void
    {
        if (isset($response->error)) {
            $message = 'Ошибка при обработке ответа: ' . $this->url . ': ' . __FILE__;
            Log::channel('tk')->error($message,  [$response->error]);
            throw new Exception($message, 200);
        }
    }
}
