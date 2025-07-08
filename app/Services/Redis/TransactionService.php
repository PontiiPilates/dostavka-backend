<?php

namespace App\Services\Redis;

use App\Traits\Json;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class TransactionService
{
    use Json;

    /**
     * Добавляет результат калькуляции.
     */
    public function addCalculationResult($hash, $calculation)
    {
        $calculation = (object) $calculation;

        $predis = Redis::connection()->client();
        $data = $this->toObject($predis->get($hash));

        try {
            $predis->multi();                                                       // начало транзакции
            $data->results = (array) $data->results;                                // чтобы сохранить свойство объекта как массив

            $data->results[$calculation->company] = $calculation->types;

            // если количество результатов достигло заявленного
            if (count($data->results) >= $data->count) {
                $data->complete = now();
                $data->is_complete = true;
            }

            $predis->setex($hash, config('custom.expire'), $this->toJson($data));
            $predis->exec();                                                        // окончание транзакции
        } catch (Exception $e) {
            $predis->discard();
            Log::channel('redis')->warning('Транзакция отклонена', [$e->getMessage()]);
            throw $e;
        }
    }
}
