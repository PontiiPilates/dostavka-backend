<?php

declare(strict_types=1);

namespace App\UseCases\TK;

use App\Builders\Kit\QueryBuilder;
use App\Builders\Kit\ResponseBuilder;
use App\Enums\Kit\KitUrlType;
use App\Interfaces\CaseInterface;
use App\Services\Clients\Tk\RestPoolClient;
use App\Services\Clients\Tk\RestPost;
use App\Services\Location\MultiLocationService;
use Illuminate\Http\Request;

class KitCase implements CaseInterface
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
