<?php

namespace App\Console\Commands;

use App\Enums\DPD\DpdUrlType;
use App\Models\City;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use SoapClient;

class FillCitiesTableCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fill-cities';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Заполнить таблицу городов';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $uri = config('companies.dpd.uri');
        $clientNumber = config('companies.dpd.client_number');
        $clientKey = config('companies.dpd.client_key');

        $countries = DB::table('countries')->get();

        $client = new SoapClient($uri . DpdUrlType::Geography->value);

        foreach ($countries as $country) {

            $parameters['request'] = [
                'auth' => [
                    'clientNumber' => $clientNumber,
                    'clientKey' => $clientKey,
                ],
                'countryCode' => $country->alpha2,
            ];

            // страны, по которым возможно получить выгрузку
            // AM
            // BY
            // KZ
            // KG
            // RU
            // UZ

            $this->line($parameters['request']['countryCode']);

            // попытка получить список городов для страны
            // перейти к следующей итерации, если не удалось получить список
            try {
                $cities = $client->getCitiesCashPay($parameters);
            } catch (\Throwable $th) {
                continue;
            }

            // если коллекция населённых пунктов отсутствует, то перейти к следующей итерации
            if (!isset($cities->return)) {
                continue;
            }

            foreach ($cities->return as $city) {
                City::updateOrCreate([
                    // 'country_id' => $country->id,  // из таблицы
                    'city_id' => $city->cityId,  // из dpd
                    'country_code' => $country->code,  // из dpd
                    'country_name' => $city->countryName, // из dpd
                    'country_fullname' => $country->fullname, // из таблицы
                    'region_code' => $city->regionCode, // из dpd
                    'region_name' => $city->regionName, // из dpd
                    'city_code' => $city->cityCode, // из dpd
                    'city_name' => $city->cityName, // из dpd
                    'index_min' => isset($city->indexMin) ? $city->indexMin : null, // из dpd
                    'index_max' => isset($city->indexMax) ? $city->indexMin : null, // из dpd
                    'alpha2' => $country->alpha2, // из таблицы
                    'alpha3' => $country->alpha3, // из таблицы
                ]);
            }
        }

        $this->info('Наполнение таблицы успешно завершено');
    }
}
