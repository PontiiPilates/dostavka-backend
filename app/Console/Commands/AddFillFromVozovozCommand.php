<?php

namespace App\Console\Commands;

use App\Enums\Vozovoz\VozovozUrlType;
use App\Models\City;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AddFillFromVozovozCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fill-vozovoz';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Добавляет идентификаторы городов для Vozovoz';

    /**
     * Execute the console command.
     * todo: после завершения всех интеграций необходимо отрефакторить структуру базы данных
     * todo: а также производить заполнения в таблицы транспортных компаний
     * todo: разработать оркестрацию выполнения данных команд, например bash
     */
    public function handle()
    {
        $this->addIdFromTerminal();
        // $this->addIdFromLocation();
    }

    /**
     * В данном варианте идентификаторы городов берутся из списка терминалов, который существенно короче
     */
    private function addIdFromTerminal()
    {
        $locations = $this->sendPost(VozovozUrlType::Terminal->value);

        $updateCount = 0;
        foreach ($locations->response->data as $location) {

            $cityName = strstr($location->location_name, ' г', true);

            $countryCode = DB::table('countries')->where('alpha2', $location->location_country)->select('code')->first();
            $city = City::where('country_code', $countryCode->code)->where('city_name', $cityName)->first();

            if ($city) {
                $update = $city->update(['city_id_vozovoz' => $location->location_guid]);

                if ($update) {
                    $updateCount++;
                    $this->line("Обновлена запись для города $cityName, count $updateCount");
                }

                continue;
            }
        }
    }

    /**
     * Оставляю этот метод на случай возможной надобности
     * Воспользоваться им слоэжно, поскольку лимит запроса - 100 записей из 320000
     * Можно использовать ассинхронные запросы, но я боюсь, что ТК просто забанит токен в этом случае
     * Возможно разуменее будет использовать базу данных терминалов, вместо всех городов
     */
    private function addIdFromLocation()
    {
        $locations = $this->sendPost(VozovozUrlType::Location->value);

        $limit = $locations->response->meta->limit;
        $offset = $locations->response->meta->offset;
        $total = 1000;

        $updateCount = 0;

        while ($offset < $total) {
            $locations = $this->sendPost($offset);

            foreach ($locations->response->data as $location) {
                $countryCode = DB::table('countries')->where('alpha2', $location->country)->select('code')->first();
                $first = City::where('country_code', $countryCode->code)->where('city_name', $location->name)->first();

                if ($first) {
                    $update = $first->update(['city_id_vozovoz' => $location->guid]);
                    !$update ?: $updateCount++;
                    $this->line("Обновлено $updateCount записей");
                    continue;
                }

                $this->line("Города $location->name не существует в таблице. Код страны $location->country");
            }

            $offset += $limit;
            $this->line($offset);
        }
    }


    /**
     * HTTP-клиент для отправки POST-запроса.
     */
    protected function sendPost($method, $offset = 0)
    {
        $url = config('companies.vozovoz.url');
        $token = config('companies.vozovoz.token');

        $parameters = [
            'object' => $method,
            'action' => 'get',
            'params' => [
                'offset' => $offset,
            ]
        ];

        try {
            $response = Http::post("$url?token=$token", $parameters);
            return $response->object();
        } catch (\Throwable $th) {
            Log::channel('tk')->error("$url: " . $th->getMessage(), $parameters);
        }
    }
}
