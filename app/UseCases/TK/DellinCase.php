<?php

namespace App\UseCases\TK;

use App\Builders\Dellin\QueryBuilder;
use App\Builders\Dellin\ResponseBuilder;
use App\Interfaces\CaseInterface;
use App\Services\Clients\Tk\RestPoolClient;
use Illuminate\Http\Request;

class DellinCase implements CaseInterface
{
    public function __construct(
        private QueryBuilder $queryBuilder,
        private ResponseBuilder $responseBuilder,
        private RestPoolClient $client,
    ) {}

    public function handle(Request $request): array
    {
        try {
            $responses = $this->client->send($request, $this->queryBuilder);
        } catch (\Throwable $th) {
            return [
                'success' => false,
                'message' => "Попытка отправить запрос для расчёта стоимости доставки",
                'data' => $th->getMessage(),
            ];
        }

        $response = $this->responseBuilder->build($responses);

        return $response;
    }
}
