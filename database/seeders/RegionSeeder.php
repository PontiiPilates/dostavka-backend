<?php

namespace Database\Seeders;

use App\Enums\Cdek\CdekUrlType;
use App\Models\Country;
use App\Services\Tk\TokenCdekService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class RegionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $countries = [
            "RU",
            "KZ",
            "KG",
            "BY",
            "AM",
            "RU",
        ];

        $tokenCdecService = new TokenCdekService();
        $token = $tokenCdecService->getActualToken();

        $response = Http::withToken($token)->get($tokenCdecService->url . CdekUrlType::Regions->value);

        foreach ($response->object() as $region) {

            // если элемент коллекции находится на территории интересующей страны
            if (in_array($region->country_code, $countries)) {

                $country = Country::where('alpha2', $region->country_code)->first();

                DB::table('regions')->insert([
                    'country_id' => $country->id,
                    'region_name' => $region->region,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
