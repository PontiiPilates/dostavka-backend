<?php

namespace App\Services\Clients\Tk;

use App\Interfaces\ClientInterface;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use stdClass;

class PostRest implements ClientInterface
{
    public function send(string $url, array $parameters): stdClass
    {
        $response = Http::post($url, $parameters);

        if ($response->status() != 200) {
            Log::channel('tk')->error("Ошибка при выполнении запроса: $url", $parameters);
            throw new Exception("Ошибка при выполнении запроса: $url", 500);
        }

        return $response->object();
    }
}
