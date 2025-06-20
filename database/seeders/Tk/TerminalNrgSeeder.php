<?php

namespace Database\Seeders\Tk;

use App\Dto\Nrg\LocationComparisonsDto;
use App\Enums\Nrg\NrgUrlType;
use App\Models\Country;
use App\Models\Location;
use App\Models\Region;
use App\Models\Tk\TerminalNrg;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;


class TerminalNrgSeeder extends Seeder
{
    private string $region = '';
    private string $type = '';

    private LocationComparisonsDto $dto;

    public function run(): void
    {
        $this->dto = new LocationComparisonsDto();

        $token = config('companies.nrg.token');
        $url = config('companies.nrg.url') . NrgUrlType::Cities->value;

        $response = Http::withHeaders(['NrgApi-DevToken' => $token])->get($url);

        foreach ($response->object()->cityList as $city) {

            $cityName = $city->name;
            $idCountry = $city->idCountry;
            $description = $city->description;

            // в некоторых идентификаторах населенных пунктов используется значение -1
            if ($idCountry < 0) {
                continue;
            }

            // в некоторых именах населенных пунктов используется указание региона в скобках
            // некоторые скобки указаны слитно, другие - через пробел
            // if (str_contains($cityName, '(')) {
            //     $cityName = strstr($cityName, '(', true);
            //     $cityName = trim($cityName);
            // }

            // в некоторых населенных пунктах используется указание страны через запятую
            // if (str_contains($cityName, ',')) {
            //     $cityName = strstr($cityName, ',', true);
            // }

            // if ($cityName == 'Санкт-Петербург' || $cityName == 'Москва' || $cityName == 'Севастополь') {
            //     $this->type = 'город';
            //     $this->region = 'Город федерального значения';
            // } else {
            //     $this->parsingRegion($description);
            //     $this->normalizeRegion();
            //     $this->normalizeLocation();
            // }

            // $country = Country::where('alpha2', $this->dto->countryCodes[$idCountry])->first();

            // $region = Region::updateOrCreate(
            //     ['region_name' => $this->region],
            //     [
            //         'country_id' => $country->id,
            //         'region_name' => $this->region
            //     ]
            // );

            // $location = Location::updateOrCreate(
            //     [
            //         'region_id' => $region->id,
            //         'name' => $cityName,
            //     ],
            //     [
            //         'country_id' => $country->id,
            //         'region_id' => $region->id,
            //         'name' => $cityName,
            //         'type' => $this->type,
            //     ]
            // );

            TerminalNrg::create([
                'identifier' => $city->id,
                'name' => $city->name,
                'description' => $city->description,
            ]);
        }
    }

    /**
     * Выполняет финальную обработку строки региона.
     */
    private function normalizeRegion()
    {
        $this->region = mb_convert_case($this->region, MB_CASE_TITLE);
        $this->region = Str::replace($this->dto->uncorrectRegionNames, $this->dto->correctRegionNames, $this->region);
        $this->region = Str::replace($this->dto->upperWords, $this->dto->getLowerWords(), $this->region);
    }

    /**
     * Выполняет финальную обработку типа населённого пункта.
     */
    private function normalizeLocation()
    {
        $this->type = Str::replace($this->dto->dirtyLocationType, $this->dto->getCleanLocationType(), $this->type);
        $this->type = Str::replace($this->dto->uncorrectTypes, $this->dto->getCorrectTypes(), $this->type);
    }

    /**
     * Определяет наименование региона и тип населённого пункта.
     */
    private function parsingRegion(string $subjects): void
    {
        $subjects = Str::lower($subjects);
        $subjects = explode(',', $subjects);

        foreach ($subjects as $subject) {

            if (Str::contains($subject, $this->dto->regionsType)) {
                $this->region = trim($subject);
                continue;
            }

            if (Str::contains($subject, $this->dto->dirtyLocationType)) {
                $this->type = trim($subject);
                continue;
            }
        }
    }
}
