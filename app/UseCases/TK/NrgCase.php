<?php

declare(strict_types=1);

namespace App\UseCases\TK;

use App\Builders\Nrg\QueryBuilder;
use App\Builders\Nrg\ResponseBuilder;
use App\Interfaces\CaseInterface;
use App\Services\Clients\Tk\RestPoolClient;
use App\Services\Location\MultiLocationService;
use Illuminate\Http\Request;

class NrgCase implements CaseInterface
{
    public function __construct(
        private QueryBuilder $queryBuilder,
        private ResponseBuilder $responseBuilder,
        private RestPoolClient $client,
        private MultiLocationService $multiLocation,
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
