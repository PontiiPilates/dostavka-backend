<?php

namespace Database\Seeders;

use App\Enums\Kit\KitUrlType;
use App\Models\Territory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class TerritorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // $file = Storage::json('assets/countries.json');

        // foreach ($file as $country) {

        //     Teritory::create([
        //         "code" => $country['code'],
        //         "name" => $country['name'],
        //         "fullname" => $country['fullname'],
        //         "alpha2" => $country['alpha2'],
        //         "alpha3" => $country['alpha3'],
        //     ]);
        // }

        $url = config('companies.kit.url');
        $token = config('companies.kit.token');

        $response = Http::withToken($token)->get($url . KitUrlType::City->value);

        $count = 0;
        foreach ($response->object() as $city) {

            if ($count >= 10) {
                break;
            }


            $parameters = [
                'code' => '01',
                'country_code' => 'AM',
            ];
            
            $response = Http::withToken($token)->get($url . KitUrlType::Regions->value, $parameters);

            dd($response->json());

            foreach ($response->object() as $region) {

                $city = Territory::updateOrCreate([

                    'alpha2' => $city->country_code,

                    'row_region_name' => $region->name,
                    'public_region_name' => $region->name,


                    'location_name' => $city->name,
                    'location_type' => $city->type,
                ]);
            }


            // это лучше делать из файлов




            $count++;
        }
    }
}
