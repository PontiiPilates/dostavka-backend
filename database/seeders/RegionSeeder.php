<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Region;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RegionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $files = [
            'Армения',
            'Беларусь',
            'Казахстан',
            'Китай',
            'Кыргызстан',
            'Россия',
        ];

        foreach ($files as $country) {

            $file = file("storage/app/assets/geo/etalon/regions/$country.txt");
            $collection = collect($file);
            $chunks = $collection->chunk(2);

            $city = '';
            $region = '';

            foreach ($chunks as $chunk) {

                $firstKey = array_key_first($chunk->all());
                $lastKey = array_key_last($chunk->all());

                $city = trim($chunk->all()[$firstKey]);
                $region = trim($chunk->all()[$lastKey]);

                $countryModel = Country::where('name', $country)->first();

                Region::create([
                    'country_id' => $countryModel->id,
                    'name' => $region
                ]);
            }
        }
    }
}
