<?php

declare(strict_types=1);

namespace App\UseCases\TK;

use App\Builders\Baikal\QueryBuilder;
use App\Builders\Baikal\ResponseBuilder;
use App\Services\Clients\Tk\RestPoolClient;
use App\Services\LocationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class BaikalsrCase extends BaseCase
{
    public function __construct(
        private QueryBuilder $queryBuilder,
        private ResponseBuilder $responseBuilder,
        private RestPoolClient $client,
        private LocationService $locationService,
    ) {}

    public function handle(Request $request): array
    {
        try {
            $responses = $this->client->send($request, $this->queryBuilder);
        } catch (\Throwable $th) {
            return [
                'success' => false,
                'message' => 'Попытка отправить запрос для расчёта стоимости доставки. ' . $th->getMessage(),
                'data' => [],
            ];
        }

        $response = $this->responseBuilder->build($responses);

        return $response;
    }
}
