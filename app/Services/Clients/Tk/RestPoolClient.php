<?php

namespace App\Services\Clients\Tk;

use App\Interfaces\RequestBuilderInterface;
use Exception;
use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RestPoolClient
{
    public function send(array $request, RequestBuilderInterface $requestBuilder): array
    {
        $responses = Http::pool(fn(Pool $pool) => $requestBuilder->build($request, $pool));

        if (empty($responses)) {
            throw new Exception('Небыло отправлено ни одного запроса. Сработала внутренняя проверка.');
        }

        foreach ($responses as $response) {
            if (!$response->ok()) {

                // здесь нет выброса исключения, чтобы не прерывать исполнение пула запросов
                // но есть логирование для мониторинга неисправностей

                Log::channel('tk')->error("Ошибка при выполнении запроса: {$response->effectiveUri()}", [
                    "request" => $response->effectiveUri()->getQuery(),
                    "response" => $response->json()
                ]);
            }
        }

        return $responses;
    }
}
