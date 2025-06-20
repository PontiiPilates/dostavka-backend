<?php

namespace Database\Seeders;

use App\Enums\Cdek\CdekUrlType;
use App\Models\Country;
use App\Models\Location;
use App\Models\Region;
use App\Services\Tk\TokenCdekService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $files = [
            'Казахстан',
            'Армения',
            'Беларусь',
            'Кыргызстан',
            'Китай',
            'Россия',
        ];

        foreach ($files as $country) {

            $file = file("storage/app/assets/geo/etalon/regions/$country.txt");
            $collection = collect($file);
            $chunks = $collection->chunk(2);

            $city = '';
            $region = '';

            foreach ($chunks as $chunk) {

                $firstItem = array_key_first($chunk->all());
                $lastItem = array_key_last($chunk->all());

                $city = trim($chunk->all()[$firstItem]);
                $region = trim($chunk->all()[$lastItem]);

                $countryModel = Country::where('name', $country)->first();

                $region = Region::updateOrCreate(
                    [
                        'country_id' => $countryModel->id,
                        'name' => $region
                    ],
                    [
                        'country_id' => $countryModel->id,
                        'name' => $region
                    ]
                );

                Location::create([
                    'country_id' => $countryModel->id,
                    'region_id' => $region->id,
                    'name' => $city,
                    'type' => 'город'
                ]);
            }
        }
    }
}
