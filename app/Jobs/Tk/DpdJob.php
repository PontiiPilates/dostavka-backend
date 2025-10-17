<?php

namespace App\Jobs\Tk;

use App\Builders\Dpd\QueryBuilder;
use App\Builders\Dpd\ResponseBuilder;
use App\Enums\CompanyType;
use App\Services\Clients\Tk\SoapDpdClient;
use App\Services\Redis\TransactionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class DpdJob implements ShouldQueue
{
    use Queueable;

    /** @var int */
    public $tries = 1;

    private QueryBuilder $queryBuilder;
    private ResponseBuilder $responseBuilder;
    private SoapDpdClient $client;
    private TransactionService $transaction;

    private string $company;

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
        $this->company = CompanyType::DPD->value;
    }

    /**
     * Execute the job.
     * 
     * В случае успеха, в транзакцию поступает результат калькуляции.
     * В случае ошибки, в транзакцию поступает сообщение от exception, которое увидит пользователь.
     * Эти exception помогают срезать 70-80% ошибок.
     * В случае с ошибкой, которая возникла минуя предусмотренные случаи в queryBuilder, ее содержание будет записано в лог tk.
     */
    public function handle(): void
    {
        try {
            $pool = $this->queryBuilder->build($this->request);

            // такое исполнение необходимо для того, чтобы отделить запросы по способу доставки
            // некий аналог pool в Guzzle
            $responses = [];
            foreach ($pool as $key => $parameters) {
                $responses[$key] = $this->client->serviseCostByParcels2($parameters);
            }

            $response = $this->responseBuilder->build($responses);
            $this->transaction->addCalculationResult($this->hash, $this->company, $response);
        } catch (\Throwable $th) {
            $this->transaction->addCalculationResult($this->hash, $this->company, [], $th->getMessage());
        }
    }
}
