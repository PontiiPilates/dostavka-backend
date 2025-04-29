<?php

namespace App\Console\Commands;

use App\Enums\Boxberry\BoxberryUrlType;
use App\Models\City;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AddFillFromBoxberryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fill-boxberry';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Добавляет идентификаторы городов для Boxberry';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $countries = ['643', '398', '112', '417', '051', '762', '860'];

        foreach ($countries as $country) {
            $cities = $this->sendPost($country);

            foreach ($cities->object() as $city) {
                $first = City::where('country_code', $city->CountryCode)->where('city_name', $city->Name)->first();

                if ($first) {
                    $first->update(['city_id_boxberry' => $city->Code]);
                    continue;
                }

                // todo: следует нормализовать таблицу, чтобы избежать невозможности добавить город
                $this->line("Города $city->Name не существует в таблице. Код страны $city->CountryCode");

                // не удалось добавить:
                // Черноголовка: 643
                // Инта: 643
                // Печора: 643
                // Усинск: 643
                // Шебекино: 643
                // Трехгорный: 643
                // Нарьян-Мар: 643
                // Лабытнанги: 643
                // Ухта: 643
                // Воркута: 643
                // Якутск: 643
                // Михнево: 643
                // Киевский: 643
                // Партизанск: 643
                // Мисайлово: 643
                // Пересвет: 643
                // Сортавала: 643
                // Судак: 643
                // Черноморское: 643
                // Новосельцы (Козинское с/пос): 643
                // Неман: 643
                // Георгиевка: 643
                // Вязьма-Брянская: 643
                // Нестеров: 643
                // Славск: 643
                // Алма-Ата: 398
                // Сеница-Копиевичи: 112
                // Каракол: 417
                // Джалал-Абад: 417
                // Беловодское: 417
                // Токмок: 417
                // Ванадзор: 051
                // Ереван: 051
                // Гюмри: 051
                // Вайк: 051
                // Джермук: 051
                // Амасия: 051
                // Ахурян: 051
                // Спитак: 051
                // Степанаван: 051
                // Ташир: 051
                // Апаран: 051
                // Аштарак: 051
                // Варденис: 051
                // Талин: 051
                // Гавар: 051
                // Мартуни: 051
                // Чамбарак: 051
                // Капан: 051
                // Мегри: 051
                // Арташат: 051
                // Сисиан: 051
                // Веди: 051
                // Масис: 051
                // Абовян: 051
                // Раздан: 051
                // Чаренцаван: 051
                // Берд: 051
                // Дилижан: 051
                // Иджеван: 051
                // Нойемберян: 051
                // Цахкаовит: 051
                // Егегнадзор: 051
                // Алаверды: 051
                // Горис: 051
                // Армавир: 051
                // Баграмян: 051
                // Эчмиадзин: 051
                // Ноемберян: 051
                // Вагаршапат: 051
                // Душанбе: 762
                // Худжанд: 762
            }
        }
    }

    /**
     * HTTP-клиент для отправки POST-запроса.
     */
    protected function sendPost($countryCode)
    {
        $url = config('companies.boxberry.url');
        $token = config('companies.boxberry.token');

        $parameters = [
            'token' => $token,
            'method' => BoxberryUrlType::ListCities->value,
            'CountryCode' => $countryCode,
        ];

        try {
            return Http::post($url, $parameters);
        } catch (\Throwable $th) {
            Log::channel('tk')->error("$url: " . $th->getMessage(), $parameters);
        }
    }
}
