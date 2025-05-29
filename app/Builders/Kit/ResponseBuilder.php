<?php

declare(strict_types=1);

namespace App\Builders\Kit;

use Exception;
use Illuminate\Support\Facades\Log;

class ResponseBuilder
{
    /**
     * Обеспечивает сборку требуемой структуры ответа.
     * 
     * @param array $responses
     * @return array
     */
    public function build(array $responses): array
    {
        $data = [];
        foreach ($responses as $key => $response) {

            $response = $response->object();

            // если ответ содержит ошибку, то происходит переход к обработке следующего ответа
            try {
                $this->checkError($response);
            } catch (\Throwable $th) {
                continue;
            }

            foreach ($response[0] as $item) {

                $data[$key][] = [
                    'tariff' => $item->name,
                    'cost' => $item->cost,
                    'days' => [
                        'from' => $item->time,
                        'to' => $item->time,
                        'date' => now()->addDays($item->time)->isoFormat('YYYY-MM-DD'),
                    ]
                ];
            }
        }

        return $data;
    }

    /**
     * Проверка наличия ошибок в ответе.
     */
    private function checkError($response): void
    {
        if (isset($response->validate)) {
            Log::channel('tk')->error('Ошибка при выполнении запроса', [$response->validate]);
            throw new Exception('Ошибка при выполнении запроса, смотри лог', 500);
        }
    }
}
