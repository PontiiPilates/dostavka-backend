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

class FinalizerLocationSeeder extends Seeder
{
    /**
     * Обновляет таблицу локаций на основе таблицы терминалов. Добавляет регионы. Связывает все таблицы.
     */
    public function run(): void
    {
        $companys = [
            CompanyType::Baikal->value => TerminalBaikal::class,
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

                // если есть почтовые индексы
                if ($terminal->index_min && $terminal->index_max) {
                    $this->updateLocation($country, $region, $district, $terminal);
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

    private function createRegion(Country $country, $terminal): Region
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

    private function createDistrict(Country $country, Region|null $region, $terminal): District
    {
        return District::updateOrCreate(
            [
                'country_id' => $country->id,
                'region_id' => $region->id ?? null,
                'name' => $terminal->district,
            ],
            [
                'country_id' => $country->id,
                'region_id' => $region->id ?? null,
                'name' => $terminal->district,
            ]
        );
    }

    /**
     * Создаёт локацию с предотвращением появления дублей посредствам снижения строгости сравнения.
     * 
     * @param Country $country
     * @param Region|null $region
     * @param District|null $district
     * @param object $terminal
     * @return Location
     */
    private function createLocation(Country $country, Region|null $region, District|null $district, object $terminal): Location
    {
        // если есть район, то пытается обнаружить и вернуть локацию с стране и районе
        if (isset($district->id)) {
            $existsDistrict = Location::where([
                ['country_id', '=', $country->id],
                ['region_id', '=', $district->id],
                ['name', '=', $terminal->name]
            ])->first();

            if ($existsDistrict) {
                return $existsDistrict;
            }
        }

        // если есть регион, то пытается обнаружить и вернуть локацию с стране и регионе
        if (isset($region->id)) {
            $existsRegion = Location::where([
                ['country_id', '=', $country->id],
                ['region_id', '=', $region->id],
                ['name', '=', $terminal->name]
            ])->first();

            if ($existsRegion) {
                return $existsRegion;
            }
        }

        // если есть только страна, то пытается обнаружить и вернуть локацию в стране
        $existsCountry = Location::where([
            ['country_id', '=', $country->id],
            ['name', '=', $terminal->name]
        ])->first();

        if ($existsCountry) {
            return $existsCountry;
        }

        // если ничего не обнаружено, то создает локацию
        return Location::create([
            'country_id' => $country->id,
            'region_id' => $region->id ?? null,
            'district_id' => $district->id ?? null,
            'name' => $terminal->name,
            'type' => $terminal->type,
            'index_min' => $terminal->index_min ?? null,
            'index_max' => $terminal->index_max ?? null,
        ]);
    }

    /**
     * Обновляет локацию почтовыми индексами.
     * 
     * @param Country $country
     * @param Region|null $region
     * @param District|null $district
     * @param object $terminal
     * @return int
     */
    private function updateLocation(Country $country, Region|null $region, District|null $district, object $terminal): int
    {
        return Location::query()
            ->where('country_id', $country->id)
            ->where('name', $terminal->name,)
            ->when($region?->id, function ($q) use ($region) {
                $q->where('region_id', $region->id);
            })
            ->when($district?->id, function ($q) use ($district) {
                $q->where('district_id', $district->id);
            })
            ->update([
                'index_min' => $terminal->index_min,
                'index_max' => $terminal->index_max,
            ]);
    }
}
