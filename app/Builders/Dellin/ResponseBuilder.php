<?php

declare(strict_types=1);

namespace App\Builders\Dellin;

use App\DTO\CalculationResultDto;
use App\Enums\CompanyType;
use App\Enums\Dellin\DellinUrlType;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ResponseBuilder
{
    private string $url;

    private $minDays;
    private $maxDays;

    private string $company;

    public function __construct()
    {
        $this->url = config('companies.dellin.url');
        $this->company = CompanyType::Dellin->value;
    }

    /**
     * Обеспечивает сборку требуемой структуры ответа.
     * 
     * @param array $responses
     * @return array
     */
    public function build(array $responses): array
    {
        $result = CalculationResultDto::filler($this->company);

        foreach ($responses as $key => $response) {
            $response = $response->object();

            $multiKey = explode(':', $key);
            $deliveryType = $multiKey[0];
            $tariff = $multiKey[1];

            // данная компания возвращает ответы по каждому тарифу в каждом способе доставки
            // можно обработать каждый ответ и в случае ошибки вывести ее напротив тарифа
            // однако в таком случае при негативном сценарии пользователь увидит список тарифив и ошибок в них
            // в этой ситуации лучше показывать только доступные тарифы
            // и если ни один из них недоступен, то выодить сообщение
            if (isset($response->errors)) {

                $errorId = Str::random(10);

                Log::channel('tk')->error(
                    sprintf('Ошибка %s при обработке ответа в тарифе %s: %s%s %s %s', $errorId, $tariff, $this->url, DellinUrlType::Calculator->value, __FILE__, __LINE__),
                    [$response->errors]
                );

                continue;
            }

            $this->datePrepare($response);

            $result['data']['success'][$deliveryType][] = CalculationResultDto::tariff(
                $tariff,
                $response->data->price ?? '',
                $this->minDays,
                $this->maxDays,
            );
        }

        // если нет успешных
        if (empty($result['data']['success'])) {
            throw new Exception(trans('messages.response.not_results'), 200);
        } else {
            return $result;
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
