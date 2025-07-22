<?php

namespace App\Jobs\Tk;

use App\Builders\Pek\QueryBuilder;
use App\Builders\Pek\ResponseBuilder;
use App\Enums\CompanyType;
use App\Services\Clients\Tk\RestPoolClient;
use App\Services\Redis\TransactionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class PekJob implements ShouldQueue
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
            $message = 'Не удалось выполнить калькуляцию по ТК ' . CompanyType::Pek->value . ': ';
            Log::channel('tk')->warning($message, [$th->getMessage() . ': ' . $th->getFile() . ': ' . $th->getLine()]);
        }
    }
}
