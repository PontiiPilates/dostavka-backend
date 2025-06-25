<?php

declare(strict_types=1);

namespace App\Builders\Baikal;

use App\Enums\Baikal\BaikalUrlType;
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
        $data = [];

        foreach ($responses as $type => $response) {
            $response = $response->object();

            // реакция на наличие ошибок запроса
            try {
                $this->checkResponseError($response);
            } catch (\Throwable $th) {
                continue;
            }

            $data[$type][] = [
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

    private function checkResponseError($response)
    {
        if (isset($response->error)) {
            Log::channel('tk')->error('Ошибка при обработке ответа: ' . $this->url . BaikalUrlType::Calculator->value, [$response->error]);
            throw new Exception('Ошибка при обработке ответа. Ответ содержит ошибку и будет исключён из итоговой сводки', 500);
        }
    }
}
