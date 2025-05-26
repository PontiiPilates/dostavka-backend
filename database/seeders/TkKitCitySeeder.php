<?php

namespace Database\Seeders;

use App\Enums\Kit\KitUrlType;
use App\Models\City;
use App\Models\Country;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class TkKitCitySeeder extends Seeder
{

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $url = config('companies.kit.url');
        $token = config('companies.kit.token');

        $response = Http::withToken($token)->get($url . KitUrlType::City->value);

        foreach ($response->object() as $item) {

            $country = Country::where('alpha2', $item->country_code)->first();

            $city = City::updateOrCreate([
                'city_name' => $item->name,
                'country_id' => $country->id
            ]);

            DB::table('tk_kit_cities')->insert([
                'city_id' => $city->id,
                'city_name' => $item->name,
                'city_code' => $item->code,
            ]);
        }
    }
}
