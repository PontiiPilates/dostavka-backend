<?php

declare(strict_types=1);

namespace App\Builders\Kit;

use App\Builders\BaseBuilder;
use App\Enums\CompanyType;
use App\Enums\Kit\KitUrlType;
use Exception;
use Illuminate\Support\Facades\Log;

class ResponseBuilder extends BaseBuilder
{
    private string $url;

    public function __construct()
    {
        $this->url = config('companies.baikal.url');
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
            'company' => CompanyType::Kit->value,
            'types' => [],
        ];

        foreach ($responses as $type => $response) {
            $response = $response->object();

            // при наличии ошибки в ответе
            try {
                $this->checkResponseError($response);
            } catch (\Throwable $th) {
                continue;
            }

            foreach ($response[0] as $item) {

                $data['types'][$type][] = [
                    "tariff" => $item->name,
                    "cost" => $item->cost,
                    "days" => [
                        "from" => $item->time,
                        "to" => $item->time,
                    ]
                ];
            }
        }

        return $data;
    }

    /**
     * Проверка наличия ошибки в ответе: выбрасывает исключение и логирует данные при обнаружении ошибки в ответе.
     * 
     * @var $response
     * @return void
     */
    private function checkResponseError($response): void
    {
        if (gettype($response) == 'string') {
            $message = 'Ошибка при обработке ответа: ' . $this->url . KitUrlType::Calculate->value . ': ' . __FILE__;
            Log::channel('tk')->error($message,  [$response]);
            throw new Exception($message, 500);
        }

        if (isset($response->validate) || gettype($response) == 'string') {
            $message = 'Ошибка при обработке ответа: ' . $this->url . KitUrlType::Calculate->value . ': ' . __FILE__;
            Log::channel('tk')->error($message,  [$response->validate]);
            throw new Exception($message, 500);
        }
    }
}
