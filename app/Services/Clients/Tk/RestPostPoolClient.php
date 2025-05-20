<?php

namespace App\Services\Clients\Tk;

use App\Interfaces\QueryPoolBuilderInterface;
use Exception;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RestPostPoolClient
{
    public function send(Request $request, QueryPoolBuilderInterface $queryBuilder): array
    {
        $responses = Http::pool(fn(Pool $pool) => $queryBuilder->build($request, $pool));

        if (empty($responses)) {
            throw new Exception("Небыло отправлено ни одного запроса по причине срабатывания ограничения(ий)");
        }

        foreach ($responses as $response) {
            if (!$response->ok()) {
                Log::channel('tk')->error("Ошибка при выполнении запроса: {$response->effectiveUri()}", [
                    "request" => $response->effectiveUri()->getQuery(),
                    "response" => $response->json()
                ]);
            }
        }

        return $responses;
    }
}
