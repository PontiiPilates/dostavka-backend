<?php

namespace App\Services\Redis;

use App\Traits\Json;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Ramsey\Collection\Collection;

class TransactionService
{
    use Json;

    /**
     * Добавляет результат калькуляции.
     */
    public function addCalculationResult($hash, $company, $result, $errors = [])
    {
        $predis = Redis::connection()->client();

        $structure = $this->toArray($predis->get($hash));

        // dump($result);

        try {
            $predis->multi(); // ! начало транзакции

            if (empty($errors)) {
                $structure['results'][$company]['errors'] = $errors;
                $structure['results'][$company]['success'] = $result['data']['success'];
                $structure['results'][$company]['is_complete'] = $result['is_complete'];
            } else {
                // если получена ошибка, то result будет пуст
                $structure['results'][$company]['errors'][] = $errors;
                $structure['results'][$company]['success'] = $result;
                $structure['results'][$company]['is_complete'] = true;
            }

            // сбор информации о незавершенных результатах
            $isCompleteCount = 0;
            foreach ($structure['results'] as $company) {
                if ($company['is_complete'] == false) {
                    $isCompleteCount++;
                }
            }

            // если не осталось незавершенных результатов
            // то происходит установка закрывающих параметров
            if ($isCompleteCount === 0) {
                $structure['complete'] = now();
                $structure['is_complete'] = true;
            }

            $predis->setex($hash, config('custom.expire'), $this->toJson($structure));
            $predis->exec(); // ! окончание транзакции
        } catch (Exception $e) {
            $predis->discard();
            Log::channel('redis')->warning('Транзакция отклонена', [$e->getMessage()]);
            throw $e;
        }
    }
}
