<?php

namespace Database\Seeders;

use App\Enums\Cdek\CdekUrlType;
use App\Models\Country;
use App\Models\Region;
use App\Services\Tk\TokenCdekService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class RegionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedingVersion2();
    }

    private function seedingVersion2()
    {
        $files = [
            'Беларусь',
            'Армения',
            'Казахстан',
            'Киргизия',
            'Китай',
            'Россия',
        ];



        foreach ($files as $country) {

            // $file = fopen("storage/app/assets/geo/etalon/regions/$country.txt", 'r');

            // $count
            // while (!feof($file)) {
            //     dump(fgets($file));
            // }
            // $lines = file("file.txt", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            // $file = Storage::file("assets/geo/etalon/regions/$country.txt");

            $file = file("storage/app/assets/geo/etalon/regions/$country.txt");
            $collection = collect($file);
            $chunks = $collection->chunk(2);

            $city = '';
            $region = '';

            foreach ($chunks as $chunk) {

                $city = trim($chunk->all()[0]);
                $region = trim($chunk->all()[1]);

                $country = Country::where('name', $country)->first();

                Region::create([
                    'country_id' => $country->id,
                    'name' => $region
                ]);
            }





            dd('end');



            Country::create([
                "code" => $country['code'],
                "name" => $country['name'],
                "fullname" => $country['fullname'],
                "alpha2" => $country['alpha2'],
                "alpha3" => $country['alpha3'],
            ]);
        }
    }

    private function seedingVersion1()
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
