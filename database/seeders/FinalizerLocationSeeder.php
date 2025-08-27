<?php

namespace Database\Seeders;

use App\Enums\CompanyType;
use App\Models\Country;
use App\Models\District;
use App\Models\Location;
use App\Models\Region;
use App\Models\Tk\TerminalBaikal;
use App\Models\Tk\TerminalBoxberry;
use App\Models\Tk\TerminalCdek;
use App\Models\Tk\TerminalDellin;
use App\Models\Tk\TerminalDpd;
use App\Models\Tk\TerminalJde;
use App\Models\Tk\TerminalKit;
use App\Models\Tk\TerminalNrg;
use App\Models\Tk\TerminalPek;
use App\Models\Tk\TerminalVozovoz;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FinalizerLocationSeeder extends Seeder
{
    /**
     * Обновляет таблицу локаций на основе таблицы терминалов. Добавляет регионы. Связывает все таблицы.
     */
    public function run(): void
    {
        // для отладки
        // Schema::disableForeignKeyConstraints();
        // DB::table('locations')->truncate();
        // Schema::enableForeignKeyConstraints();

        $companys = [
            CompanyType::Baikal->value => TerminalBaikal::class,
            CompanyType::Boxberry->value => TerminalBoxberry::class,
            CompanyType::Cdek->value => TerminalCdek::class,
            CompanyType::Dellin->value => TerminalDellin::class,
            CompanyType::DPD->value => TerminalDpd::class,
            CompanyType::Jde->value => TerminalJde::class,
            CompanyType::Kit->value => TerminalKit::class,
            CompanyType::Nrg->value => TerminalNrg::class,
            CompanyType::Pek->value => TerminalPek::class,
            CompanyType::Vozovoz->value => TerminalVozovoz::class,
        ];

        foreach ($companys as $company => $model) {
            $terminals = $model::get();

            $iterable = 0;
            $timeStart = Carbon::now();
            foreach ($terminals as $terminal) {

                $country = Country::where('alpha2', $terminal->country)->first();

                // региона может не быть, допустимо поместить null в locations
                $region = null;
                if ($terminal->region) {
                    $region = $this->createRegion($country, $terminal);
                }

                // района может не быть, допустимо поместить null в locations
                $district = null;
                if ($terminal->district) {
                    $district = $this->createDistrict($country, $region, $terminal);
                }

                $location = $this->createLocation($country, $region, $district, $terminal);

                $terminal->update(['location_id' => $location->id]);

                $iterable++;
            }
            $timeEnd = Carbon::now();
            $executionTime = $timeStart->diffInSeconds($timeEnd);
            $executionTime = number_format((float) $executionTime, 1, '.');

            if ($iterable > 0) {
                $this->command->info("$company: сформировано $iterable локаций, $executionTime сек.");
            } else {
                $this->command->line("$company: сформировано $iterable локаций, $executionTime сек.");
            }
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

    private function createDistrict($country, $region, $terminal): District
    {
        return District::updateOrCreate(
            [
                'region_id' => $region->id ?? null,
                'name' => $terminal->district,
                'country_id' => $country->id,
            ],
            [
                'region_id' => $region->id ?? null,
                'name' => $terminal->district,
                'country_id' => $country->id,
            ]
        );
    }

    private function createLocation($country, $region, $district, $terminal): Location
    {
        return Location::updateOrCreate(
            [
                'country_id' => $country->id,
                'region_id' => $region->id ?? null,
                'district_id' => $district->id ?? null,
                'name' => $terminal->name,
            ],
            [
                'country_id' => $country->id,
                'region_id' => $region->id ?? null,
                'district_id' => $district->id ?? null,
                'name' => $terminal->name,
                'type' => $terminal->type,
            ]
        );
    }
}
