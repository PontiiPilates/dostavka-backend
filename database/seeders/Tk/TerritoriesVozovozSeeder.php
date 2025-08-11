<?php

namespace Database\Seeders\Tk;

use App\Models\Country;
use App\Models\District;
use App\Models\Location;
use App\Models\Region;
use App\Models\Tk\TerminalVozovoz;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TerritoriesVozovozSeeder extends Seeder
{
    /**
     * Обновляет таблицу локаций на основе таблицы терминалов. Добавляет регионы. Связывает все таблицы.
     */
    public function run(): void
    {
        $terminals = TerminalVozovoz::get();

        foreach ($terminals as $terminal) {

            $country = Country::where('alpha2', $terminal->country)->first();

            $region = $this->createRegion($country, $terminal);

            // района может не быть
            $district = null;
            if (isset($terminal->district) && $terminal->district != null) {
                $district = $this->createDistrict($region, $terminal);
            }

            $location = $this->createLocation($country, $region, $district, $terminal);

            $terminal->update(['location_id' => $location->id]);
        }
    }

    private function createRegion($country, $terminal): Region
    {
        return Region::updateOrCreate(
            [
                'country_id' => $country->id,
                'name' => $terminal->region,
            ],
            [
                'country_id' => $country->id,
                'name' => $terminal->region,
            ]
        );
    }

    private function createDistrict($region, $terminal): District
    {
        return District::updateOrCreate(
            [
                'region_id' => $region->id,
                'name' => $terminal->district,
            ],
            [
                'region_id' => $region->id,
                'name' => $terminal->district,
            ]
        );
    }

    private function createLocation($country, $region, $district, $terminal): Location
    {
        return Location::updateOrCreate(
            [
                'region_id' => $region->id,
                'district_id' => $district->id ?? null,
                'name' => $terminal->name,
            ],
            [
                'country_id' => $country->id,
                'region_id' => $region->id,
                'district_id' => $district->id ?? null,
                'name' => $terminal->name,
                'type' => $terminal->type,
            ]
        );
    }
}
