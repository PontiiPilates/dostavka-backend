<?php

namespace Database\Seeders;

use App\Enums\Pek\PekUrlType;
use App\Models\City;
use App\Models\Country;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class TkPekTerminalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $url = config('companies.pek.url');
        $user = config('companies.pek.user');
        $password = config('companies.pek.password');

        $response = Http::withBasicAuth($user, $password)->post($url . PekUrlType::Terminals->value);

        foreach ($response->object()->branches as $item) {

            foreach ($item->cities as $city) {

                foreach ($city->divisions as $division) {
                    $divisionUuid = $division;

                    foreach ($item->divisions as $division) {

                        if ($division->id === $divisionUuid) {

                            foreach ($division->warehouses as $warehouse) {

                                $country = Country::where('code', $item->countryOfRegistrationCode)->first();

                                $modelCity = City::updateOrCreate([
                                    'city_name' => $this->cleanCityName($city->title),
                                    'country_id' => $country->id
                                ]);

                                DB::table('tk_pek_terminals')->insert([
                                    'city_id' => $modelCity->id,
                                    'city_name' => $modelCity->city_name,
                                    'terminal_id' => $warehouse->id,
                                    'max_weight' => $warehouse->maxWeight,
                                    'max_volume' => $warehouse->maxVolume,
                                    'max_weight_per_place' => $warehouse->maxWeightPerPlace,
                                    'max_dimension' => $warehouse->maxDimension,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ]);
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Возвращает чистое название населённого пункта, без указания региона в скобочках.
     */
    private function cleanCityName(string $name): string
    {
        $withouthRegion = strstr($name, '(', true);

        if ($withouthRegion) {
            return trim($withouthRegion);
        }

        return trim($name);
    }
}
