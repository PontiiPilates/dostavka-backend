<?php

declare(strict_types=1);

namespace App\Builders\Dellin;

use App\Enums\CompanyType;
use App\Enums\Dellin\DellinUrlType;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class ResponseBuilder
{
    private string $url;

    private $minDays;
    private $maxDays;

    public function __construct()
    {
        $this->url = config('companies.dellin.url');
    }

    /**
     * Обеспечивает сборку требуемой структуры ответа.
     * 
     * @param array $responses
     * @return array
     */
    public function build(array $responses): array
    {
        $data = [
            'company' => CompanyType::Dellin->value,
            'types' => [],
        ];

        foreach ($responses as $key => $response) {
            $response = $response->object();

            $multiKey = explode(':', $key);
            $type = $multiKey[0];
            $tariff = $multiKey[1];

            // при наличии ошибки в ответе
            try {
                $this->checkResponseError($response);
            } catch (\Throwable $th) {
                continue;
            }

            $this->datePrepare($response);

            $data['types'][$type][] = [
                "tariff" => $tariff,
                "cost" => $response->data->price ?? null,
                "days" => [
                    "from" => $this->minDays,
                    "to" => $this->maxDays,
                ]
            ];
        }

        return $data;
    }

    private function checkResponseError($response)
    {
        if (isset($response->errors)) {
            $message = 'Ошибка при обработке ответа: ' . $this->url . DellinUrlType::Calculator->value;
            Log::channel('tk')->error($message,  $response->errors);
            throw new Exception($message, 500);
        }
    }

    /**
     * Устанавливает минимальную и максимальную дату доставки на основе парсинга грязного массива доступных дат.
     */
    private function datePrepare($response): void
    {
        $shipmentDate = $response->data->orderDates->derivalFromOspSender;

        $dates = [];
        foreach ($response->data->orderDates as $date) {

            // встречается null - его обработка не требуется
            if ($date == null) {
                continue;
            }

            $parse = Carbon::parse($date);

            // встречается время - его обработка не требуется
            if (strpos($date, ':')) {
                continue;
            }

            $dates[] = $parse->toObject()->timestamp;
        }

        uasort($dates, function ($a, $b) {
            if ($a == $b) {
                return 0;
            }
            return ($a < $b) ? -1 : 1;
        });

        $first = array_key_first($dates);
        $last = array_key_last($dates);

        $minDate = Carbon::createFromTimestamp($dates[$first]);
        $maxDate = Carbon::createFromTimestamp($dates[$last]);

        $this->minDays = Carbon::parse($shipmentDate)->diff($minDate)->days;
        $this->maxDays = Carbon::parse($shipmentDate)->diff($maxDate)->days;
    }
}
