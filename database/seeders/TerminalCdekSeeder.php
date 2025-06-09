<?php

namespace Database\Seeders;

use App\Enums\Cdek\CdekUrlType;
use App\Models\City;
use App\Models\Country;
use App\Services\Tk\TokenCdekService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class TerminalCdekSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tokenCdecService = new TokenCdekService();
        $token = $tokenCdecService->getActualToken();

        $response = Http::withToken($token->token)->get($tokenCdecService->url . CdekUrlType::Cities->value);

        foreach ($response->object() as $city) {

            $country = Country::where('alpha2', $city->country_code)->first();

            $modelCity = City::updateOrCreate([
                'city_name' => $city->city,
                'country_id' => $country->id
            ]);

            DB::table('terminals_cdek')->insert([
                'city_id' => $modelCity->id,
                'city_name' => $modelCity->city_name,
                'terminal_id' => $city->code,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
