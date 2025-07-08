<?php

namespace App\Jobs\Tk;

use App\Builders\Kit\QueryBuilder;
use App\Builders\Kit\ResponseBuilder;
use App\Services\Clients\Tk\RestPoolClient;
use App\Services\Redis\TransactionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class KitJob implements ShouldQueue
{
    use Queueable;

    /** @var int */
    public $tries = 1;

    private QueryBuilder $queryBuilder;
    private ResponseBuilder $responseBuilder;
    private RestPoolClient $client;
    private TransactionService $transaction;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private array $request,
        private string $hash,
    ) {
        $this->queryBuilder = new QueryBuilder();
        $this->responseBuilder = new ResponseBuilder();
        $this->client = new RestPoolClient();
        $this->transaction = new TransactionService();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $response = [];
        try {
            $responses = $this->client->send($this->request, $this->queryBuilder);
            $response = $this->responseBuilder->build($responses);
            $this->transaction->addCalculationResult($this->hash, $response);
        } catch (\Throwable $th) {
            Log::channel('tk')->error('Не удалось выполнить калькуляцию по ТК Kit: ', [$th->getMessage()]);
        }
    }
}
