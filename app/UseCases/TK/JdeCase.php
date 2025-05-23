<?php

declare(strict_types=1);

namespace App\UseCases\TK;

use App\Builders\Jde\QueryBuilder;
use App\Builders\Jde\ResponseBuilder;
use App\Interfaces\CaseInterface;
use App\Services\Clients\Tk\RestPoolClient;
use App\Services\Location\MultiLocationService;
use Illuminate\Http\Request;

class JdeCase implements CaseInterface
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
                'message' => "Попытка отправить запрос для расчёта стоимости доставки",
                'data' => $th->getMessage(),
            ];
        }

        $response = $this->responseBuilder->build($responses);

        return $response;
    }
}
