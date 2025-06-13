<?php

namespace Database\Seeders\Tk;

use App\Enums\Nrg\NrgUrlType;
use App\Models\City;
use App\Models\Country;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class TerminalNrgSeeder extends Seeder
{
    private array $countryIds = [
        0 => "RU",
        86015311036992 => "RU",
        86015311037009 => "KZ",
        86015311037004 => "KG",
        86015311036993 => "BY",
        86015311037003 => "AM",
        86015311037018 => "RU",
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $token = config('companies.nrg.token');
        $url = config('companies.nrg.url') . NrgUrlType::Cities->value;

        $response = Http::withHeaders(['NrgApi-DevToken' => $token])->get($url);

        // $this->allCountryCodes($response);

        foreach ($response->object()->cityList as $city) {

            // в некоторых идентификаторах населенных пунктов используется значение -1
            if ($city->idCountry < 0) {
                continue;
            }

            $cityName = $city->name;

            // в некоторых именах населенных пунктов используется указание региона в скобках
            if (str_contains($cityName, '(')) {
                $cityName = strstr($cityName, ' (', true);
            }

            // в некоторых населенных пунктах используется указание страны через запятую
            if (str_contains($cityName, ',')) {
                $cityName = strstr($cityName, ',', true);
            }

            $country = Country::where('alpha2', $this->countryIds[$city->idCountry])->first();

            $modelCity = City::updateOrCreate([
                'city_name' => $cityName,
                'country_id' => $country->id
            ]);

            DB::table('terminals_nrg')->insert([
                'city_id' => $modelCity->id,
                'city_name' => $modelCity->city_name,
                'terminal_id' => $city->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Позволяет узнать коды стран, с которыми в данный момент работает тк.
     * 
     * Работает в режиме ручного подключения на случай обновления.
     */
    private function allCountryCodes($response)
    {
        $this->countryIds = [];

        foreach ($response->object()->cityList as $city) {

            if (!in_array($city->idCountry, $this->countryIds)) {
                array_push($this->countryIds, $city->idCountry);
            }
        }

        dd($this->countryIds);
    }
}
