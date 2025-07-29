<?php

namespace Database\Seeders\Tk;

use App\Enums\Pochta\PochtaUrlType;
use App\Models\Tk\TariffPochta;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class TariffPochtaSeeder extends Seeder
{
    private array $badTariffs = [];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // особенность данной тк в том, что не существует метода для получения всех тарифов
        // есть метод для получения информации по одному тарифу
        // коды тарифов (объекты расчётов) перечислены в документации: https://delivery.pochta.ru/post-calculator-api.pdf?836
        // эти корды можно увидеть на страницах калькуляции: https://tariff.pochta.ru/
        // у тарифа отсутствует возможность определить способ доставки, который можно к нему применить
        // поэтому информацию по всем тарифам пришлось поместить в файл и обучить нейросеть, чтобы она присвоила тарифу способ доставки

        // $this->allTariffToJson();
        $this->seedTariffs();
    }

    /**
     * Сохраняет информацию о всех тарифах в json.
     * 
     * Этот метод необходим для того, чтобы проанализировать собранные тарифы с помощью нейросети.
     * Затем сформировать на основе этого анализа список аттрибутов, которые принадлежат тарифу.
     */
    private function allTariffToJson(): void
    {
        $data = [];
        foreach ($this->attributes() as $object) {

            $object = (object) $object;

            $url = config('companies.pochta.url') . PochtaUrlType::DictionaryObject->value;
            $parameters = ['id' => $object->object];

            $response = Http::get($url, $parameters);

            $data['tariffs'][] = $response->json();
        }

        $data = json_encode($data, JSON_UNESCAPED_UNICODE);

        $save = Storage::put('app/assets/tk/pochta/tariffs.json', $data);

        dump($save);
    }

    private function seedTariffs()
    {
        foreach ($this->attributes() as $objectId => $attributes) {

            $attributes = (object) $attributes;

            $url = config('companies.pochta.url') . PochtaUrlType::DictionaryObject->value;
            $parameters = ['id' => $objectId];

            $response = Http::get($url, $parameters)->object();

            // обработка случая, когда по тарифу (объекту расчёта) нет информации
            if (!isset($response->object)) {
                $this->badTariffs[] = $objectId;
                continue;
            }

            TariffPochta::create(array_filter([
                'object' => $objectId,
                'name' => $response->object[0]->name,
                'sumoc' => isset($attributes->sumoc) ? $attributes->sumoc : null,
                'sumnp' => isset($attributes->sumnp) ? $attributes->sumnp : null,
                'min_weight' => isset($attributes->min_weight) ? $attributes->min_weight : null,
                'max_weight' => isset($attributes->max_weight) ? $attributes->max_weight : null,
                'country_to' => isset($attributes->country_to) ? $attributes->country_to : null,
                'ss' => isset($attributes->ss) ? $attributes->ss : null,
                'sd' => isset($attributes->sd) ? $attributes->sd : null,
                'ds' => isset($attributes->ds) ? $attributes->ds : null,
                'dd' => isset($attributes->dd) ? $attributes->dd : null,
            ]));
        }

        dump('Отсутствует информация по следующим объектам рассчёта: ', $this->badTariffs);
    }

    /**
     * Список тарифов и их аттрибутов, собранный вручную и на основе анализа ии.
     */
    private function attributes(): array
    {
        return [
            '2000' => [
                'max_weight' => 100,
                "ss" => true,
            ],
            '2020' => [
                'max_weight' => 100,
                'sumoc' => true,
                "ss" => true,
            ],
            '2040' => [
                'max_weight' => 100,
                'sumoc' => true,
                'sumnp' => true,
                "ss" => true,
            ],
            '15010' => [
                'max_weight' => 500,
                "ss" => true,
            ],
            '15020' => [
                'max_weight' => 500,
                'sumoc' => true,
                "ss" => true,
            ],
            '15040' => [
                'max_weight' => 500,
                'sumoc' => true,
                'sumnp' => true,
                "ss" => true,
                "sd" => true
            ],
            '6000' => [
                "ss" => true,
            ],
            '6010' => [
                "ss" => true,
            ],
            '8010' => [
                'max_weight' => 7000,
                "ss" => true,
            ],
            '3000' => [
                'min_weight' => 100,
                'max_weight' => 5000,
                "ss" => true,
            ],
            '3010' => [
                'min_weight' => 100,
                'max_weight' => 5000,
                "ss" => true,
            ],
            '3020' => [
                'max_weight' => 5000,
                'sumoc' => true,
                "ss" => true,
            ],
            '3040' => [
                'max_weight' => 5000,
                'sumoc' => true,
                'sumnp' => true,
                "ss" => true,
                "sd" => true
            ],
            '16010' => [
                'max_weight' => 2000,
                "ss" => true,
                "sd" => true,
            ],
            '16020' => [
                'max_weight' => 2500,
                'sumoc' => true,
                "ss" => true,
                "sd" => true,
            ],
            '16040' => [
                'max_weight' => 2500,
                'sumoc' => true,
                'sumnp' => true,
                "ss" => true,
                "sd" => true,
                "dd" => true,
            ],
            '303000' => [
                'max_weight' => 100,
                "ss" => true,
            ],
            '303010' => [
                'max_weight' => 100,
                "ss" => true,
            ],
            '303011' => [
                'country_to' => true,
                "ss" => true,
            ],
            '4030' => [
                'max_weight' => 20000,
                "ss" => true,
                "sd" => true,
                "dd" => true,
            ],
            '4020' => [
                'max_weight' => 20000,
                'sumoc' => true,
                "ss" => true,
                "sd" => true,
                "dd" => true,
            ],
            '4040' => [
                'max_weight' => 20000,
                'sumoc' => true,
                'sumnp' => true,
                "ss" => true,
                "sd" => true,
                "dd" => true,
            ],
            '47030' => [
                'max_weight' => 20000,
                "ss" => true,
                "sd" => true,
                "ds" => true,
                "dd" => true
            ],
            '47020' => [
                'max_weight' => 20000,
                'sumoc' => true,
                "ss" => true,
                "sd" => true,
                "ds" => true,
                "dd" => true
            ],
            '47040' => [
                'max_weight' => 20000,
                'sumoc' => true,
                'sumnp' => true,
                "id" => 47040,
                "ss" => true,
                "sd" => true,
                "ds" => true,
                "dd" => true
            ],
            '23030' => [
                'max_weight' => 20000,
                "ss" => true,
                "sd" => true,
                "ds" => true,
                "dd" => true,
            ],
            '23020' => [
                'max_weight' => 20000,
                'sumoc' => true,
                "ss" => true,
                "sd" => true,
                "ds" => true,
                "dd" => true,
            ],
            '23040' => [
                'max_weight' => 20000,
                'sumoc' => true,
                'sumnp' => true,
                "ss" => true,
                "sd" => true,
                "ds" => true,
                "dd" => true,
            ],
            '51030' => [
                'max_weight' => 20000,
                "ss" => true,
                "sd" => true
            ],
            '51020' => [
                'max_weight' => 20000,
                'sumoc' => true,
                "ss" => true,
                "sd" => true
            ],
            '24030' => [
                'max_weight' => 31500,
                "dd" => true,
            ],
            '24020' => [
                'max_weight' => 31500,
                'sumoc' => true,
                "dd" => true,
            ],
            '24040' => [
                'max_weight' => 31500,
                'sumoc' => true,
                'sumnp' => true,
                "dd" => true
            ],
            '30030' => [
                'max_weight' => 31500,
                "dd" => true,
            ],
            '30020' => [
                'max_weight' => 31500,
                'sumoc' => true,
                "dd" => true,
            ],
            '31030' => [
                'max_weight' => 31500,
                "dd" => true,
            ],
            '31020' => [
                'max_weight' => 31500,
                'sumoc' => true,
                "dd" => true
            ],
            '40000' => [
                'min_weight' => 1000,
                'max_weight' => 500000000,
                // 'size' => ''
                "ss" => true,
            ],
            '54020' => [
                'max_weight' => 500000,
                'sumoc' => true,
                "dd" => true,
            ],
            '54060' => [
                'max_weight' => 500000,
                'sumoc' => true,
                'sumnp' => true,
                "dd" => true
            ],
            '7030' => [
                'max_weight' => 31500,
                "sd" => true,
                "dd" => true,
            ],
            '7020' => [
                'max_weight' => 31500,
                'sumoc' => true,
                "sd" => true,
                "dd" => true,
            ],
            '7040' => [
                'max_weight' => 31500,
                'sumoc' => true,
                'sumnp' => true,
                "sd" => true,
                "dd" => true,
            ],
            '7000' => [
                "ss" => true,
            ],
            '41030' => [
                'max_weight' => 500000,
                "sd" => true,
                "dd" => true,
            ],
            '41020' => [
                'max_weight' => 500000,
                'sumoc' => true,
                "sd" => true,
                "dd" => true,
            ],
            '41040' => [
                "ss" => true,
            ],
            '52030' => [
                'max_weight' => 31500,
                "dd" => true
            ],
            '52020' => [
                'max_weight' => 31500,
                'sumoc' => true,
                "dd" => true
            ],
            '2001' => [
                'max_weight' => 2000,
                'country_to' => true,
                "ss" => true
            ],
            '2011' => [
                'max_weight' => 2000,
                'country_to' => true,
                "ss" => true,
            ],
            '2021' => [
                'max_weight' => 2000,
                'country_to' => true,
                'sumoc' => true,
                "ss" => true,
            ],
            '2041' => [
                'country_to' => true,
                'sumoc' => true,
                'sumnp' => true,
                "ss" => true,
            ],
            '6001' => [
                'country_to' => true,
                "ss" => true,
            ],
            '6011' => [
                'country_to' => true,
                "ss" => true,
            ],
            '8011' => [
                'max_weight' => 7000,
                'country_to' => true,
                "ss" => true,
            ],
            '3001' => [
                'max_weight' => 5000,
                'country_to' => true,
                "ss" => true,
            ],
            '3011' => [
                'max_weight' => 5000,
                'country_to' => true,
                "ss" => true,
                "sd" => true
            ],
            '4031' => [
                'country_to' => true,
                "ss" => true,
                "sd" => true,
                "ds" => true,
                "dd" => true,
            ],
            '4021' => [
                'country_to' => true,
                'sumoc' => true,
                "ss" => true,
                "sd" => true,
                "ds" => true,
                "dd" => true,
            ],
            '4041' => [
                'country_to' => true,
                'sumoc' => true,
                'sumnp' => true,
                "ss" => true,
                "sd" => true,
            ],
            '7031' => [
                'country_to' => true,
                "ds" => true,
                "dd" => true,
            ],
            '5001' => [
                'country_to' => true,
                "ss" => true,
                "sd" => true,
                "ds" => true,
                "dd" => true,
            ],
            '5011' => [
                'country_to' => true,
                "ss" => true,
                "sd" => true,
                "ds" => true,
                "dd" => true,
            ],
            '9001' => [
                'max_weight' => 14500,
                'country_to' => true,
                "ss" => true,
            ],
            '9011' => [
                'max_weight' => 14500,
                'country_to' => true,
                "ss" => true,
                "sd" => true
            ],
            // ['object' => 2010, 'max_weight' => 100], // приложение не предусматривает наличие заказного формата
            // ['object' => 11000], // невозможно получить информацию
            // ['object' => 46000], // нужен инн-договор
            // ['object' => 46010], // нужен инн-договор
            // ['object' => 11010], // невозможно получить информацию
            // ['object' => 32010], // невозможно получить информацию
            // ['object' => 33010], // невозможно получить информацию
            // ['object' => 36000], // незьзя доставить
            // ['object' => 37000], // незьзя доставить
            // ['object' => 27030], // нельзя принять
            // ['object' => 27020], // нельзя принять
            // ['object' => 27040], // нельзя принять
            // ['object' => 29030], // услуга тарификации не осуществляется после 12.04.2022
            // ['object' => 29020], // услуга тарификации не осуществляется после 12.04.2022
            // ['object' => 29040], // услуга тарификации не осуществляется после 12.04.2022
            // ['object' => 28030], // услуга тарификации не осуществляется после 12.04.2022
            // ['object' => 28020], // услуга тарификации не осуществляется после 12.04.2022
            // ['object' => 28040], // услуга тарификации не осуществляется после 12.04.2022
            // ['object' => 4060, 'max_weight' => 20000], // приложение не предусматривает наличие обязательного платежа
            // ['object' => 47060, 'max_weight' => 20000], // приложение не предусматривает наличие обязательного платежа
            // ['object' => 23060, 'max_weight' => 20000], // приложение не предусматривает наличие обязательного платежа
            // ['object' => 23080], // можно доставить только в почтомат
            // ['object' => 23090], // можно доставить только в почтомат
            // ['object' => 23100], // можно доставить только в почтомат
            // ['object' => 24060, 'max_weight' => 31500], // приложение не предусматривает наличие обязательного платежа
            // ['object' => 39000], // доставка из в невозможна
            // ['object' => 53030], // услуга тарификации не осуществляется после 01.01.2025
            // ['object' => 53070], // услуга тарификации не осуществляется после 01.01.2025
            // ['object' => 7060, 'max_weight' => 31500], // приложение не предусматривает наличие обязательного платежа
            // ['object' => 34030], // нельзя принять
            // ['object' => 34020], // нельзя принять
            // ['object' => 34040], // нельзя принять
            // ['object' => 34060], // нельзя принять
            // ['object' => 52060, 'max_weight' => 31500], // приложение не предусматривает наличие обязательного платежа
            // ['object' => 19000], // не действует на дату 24.07.2025
            // ['object' => 19010], // не действует на дату 24.07.2025
            // ['object' => 22000], // не действует на дату 24.07.2025
            // ['object' => 22010], // не действует на дату 24.07.2025
            // ['object' => 305200], // тариф для перевода денег
            // ['object' => 4032], // услуга тарификации не осуществляется
            // ['object' => 4022], // услуга тарификации не осуществляется
            // ['object' => 4042], // услуга тарификации не осуществляется
            // ['object' => 7032], // услуга тарификации не осуществляется
            // ['object' => 7022], // услуга тарификации не осуществляется
            // ['object' => 7042], // услуга тарификации не осуществляется
            // ['object' => 5002], // услуга тарификации не осуществляется
            // ['object' => 5012], // услуга тарификации не осуществляется
        ];
    }
}
