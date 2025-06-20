<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $file = Storage::json('assets/geo/etalon/countries.json');

        foreach ($file as $country) {

            Country::create([
                "code" => $country['code'],
                "name" => $country['name'],
                "fullname" => $country['fullname'],
                "alpha2" => $country['alpha2'],
                "alpha3" => $country['alpha3'],
            ]);
        }
    }
}
