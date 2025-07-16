<?php

namespace App\Jobs\Tk;

use App\Builders\Dpd\QueryBuilder;
use App\Builders\Dpd\ResponseBuilder;
use App\Enums\CompanyType;
use App\Services\Clients\Tk\SoapDpdClient;
use App\Services\Redis\TransactionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class DpdJob implements ShouldQueue
{
    use Queueable;

    /** @var int */
    public $tries = 1;

    private QueryBuilder $queryBuilder;
    private ResponseBuilder $responseBuilder;
    private SoapDpdClient $client;
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
        $this->client = new SoapDpdClient();
        $this->transaction = new TransactionService();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $response = [];
        try {
            $pool = $this->queryBuilder->build($this->request);

            // такое исполнение необходимо для того, чтобы отделить запросы по способу доставки
            // некий аналог pool в Guzzle
            $responses = [];
            foreach ($pool as $key => $parameters) {
                $responses[$key] = $this->client->serviseCostByParcels2($parameters);
            }

            $response = $this->responseBuilder->build($responses);
            $this->transaction->addCalculationResult($this->hash, $response);
        } catch (\Throwable $th) {
            $message = 'Не удалось выполнить калькуляцию по ТК ' . CompanyType::DPD->value . ': ';
            Log::channel('tk')->warning($message, [$th->getMessage() . ': ' . $th->getFile() . ': ' . $th->getLine()]);
        }
    }
}
