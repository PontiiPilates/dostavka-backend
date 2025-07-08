<?php

declare(strict_types=1);

namespace App\Builders\Baikal;

use App\Enums\Baikal\BaikalUrlType;
use App\Enums\CompanyType;
use Exception;
use Illuminate\Support\Facades\Log;

class ResponseBuilder
{
    private string $url;

    public function __construct()
    {
        $this->url = config('companies.baikal.url');
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
            'company' => CompanyType::Baikal->value,
            'types' => [],
        ];

        foreach ($responses as $type => $response) {
            $response = $response->object();

            // реакция на наличие ошибок запроса
            try {
                $this->checkResponseError($response);
            } catch (\Throwable $th) {
                continue;
            }

            $data['types'][$type][] = [
                "tariff" => 'Автоперевозка',
                "cost" => $response->total,
                "days" => [
                    "from" => $response->transit->int,
                    "to" => $response->transit->int,
                ]
            ];
        }

        return $data;
    }

    /**
     * Проверяет наличие ошибок запроса.
     */
    private function checkResponseError($response)
    {
        if (isset($response->error)) {
            $message = 'Ошибка при обработке ответа: ' . $this->url . BaikalUrlType::Calculator->value . ': ' . __FILE__;
            Log::channel('tk')->error($message,  [$response->error]);
            throw new Exception($message, 500);
        }
    }
}
