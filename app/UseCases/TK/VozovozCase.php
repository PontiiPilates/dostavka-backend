<?php

declare(strict_types=1);

namespace App\UseCases\TK;

use App\Builders\Vozovoz\QueryBuilder;
use App\Builders\Vozovoz\ResponseBuilder;
use App\interfaces\CaseInterface;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class VozovozCase extends BaseCase implements CaseInterface
{
    public function __construct(
        private QueryBuilder $queryBuilder,
        private ResponseBuilder $responseBuilder,
    ) {}

    /**
     * Возвращает расчёт стоимости доставки. 
     */
    public function handle(Request $request): array
    {
        try {
            $responses = Http::pool(fn(Pool $pool) => $this->queryBuilder->build($pool, $request));
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
