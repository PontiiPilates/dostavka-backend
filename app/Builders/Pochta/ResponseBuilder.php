<?php

declare(strict_types=1);

namespace App\Builders\Pochta;

use App\Enums\CompanyType;
use App\Enums\Pochta\PochtaUrlType;
use Exception;
use Illuminate\Support\Facades\Log;

class ResponseBuilder
{
    private string $url;

    public function __construct()
    {
        $this->url = config('companies.pochta.url') . PochtaUrlType::Calculate->value;
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
            'company' => CompanyType::Pochta->value,
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

            $data['types'][$type][] = [
                "tariff" => $response->name,
                "cost" => isset($response->paynds) ? $response->paynds / 100 : null,
                "days" => [
                    "from" => $response->delivery->min ?? null,
                    "to" => $response->delivery->max ?? null,
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
        if (isset($response->errors)) {
            $message = 'Ошибка при обработке ответа: ' . $this->url . ': ' . __FILE__;
            Log::channel('tk')->error($message,  [$response->errors]);
            throw new Exception($message, 200);
        }
    }
}
