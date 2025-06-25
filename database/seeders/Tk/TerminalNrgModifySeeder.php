<?php

namespace Database\Seeders\Tk;

use App\Dto\Nrg\LocationComparisonsDto;
use App\Enums\Nrg\NrgUrlType;
use App\Models\Country;
use App\Models\Location;
use App\Models\Region;
use App\Models\Tk\TerminalNrg;
use Exception;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;


class TerminalNrgModifySeeder extends Seeder
{
    private string $region = '';
    private string $type = '';

    private array $candidatsToUpdate = [];

    private LocationComparisonsDto $dto;

    public function run(): void
    {
        $this->dto = new LocationComparisonsDto();

        $token = config('companies.nrg.token');
        $url = config('companies.nrg.url') . NrgUrlType::Cities->value;

        $response = Http::withHeaders(['NrgApi-DevToken' => $token])->get($url);

        foreach ($response->object()->cityList as $city) {

            $location = $city->name;
            $countryCode = $city->idCountry;
            $region = $city->description;

            // в некоторых идентификаторах населенных пунктов используется значение -1
            if ($countryCode < 0) {
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

            $country = Country::where('alpha2', $this->dto->countryCodes[$countryCode])->first();

            try {
                $this->checkExists($location);
            } catch (\Throwable $th) {

                $exists = Region::where(function ($q) use ($region) {
                    $explode = explode(',', $region);
                    foreach ($explode as $item) {
                        $q->orWhere('name', 'LIKE', trim($item));
                    }
                })->exists();

                if (!$exists) {
                    $this->candidatsToUpdate[] = $location . ': ' . $region;
                }

                // ! Осталось почти 400 записей, которые возможно найдут свои места, если из них вырезать город, село, пгт и тд.
            }

            // dd($country);

            // $region = Region::updateOrCreate(
            //     ['name' => $this->region],
            //     [
            //         'country_id' => $country->id,
            //         'name' => $description
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
            //         'type' => $city->type,
            //     ]
            // );

            // TerminalNrg::updateOrCreate(
            //     [
            //         'name' => $cityName,
            //         'identifier' => $city->id,
            //     ],
            //     [
            //         'location_id' => $location->id,
            //         'name' => $cityName,
            //         'identifier' => $city->id,
            //     ]
            // );
        }

        dump('ТК хочет добавить следующие локации', $this->candidatsToUpdate);
    }

    private function checkExists($location)
    {
        $exists = Location::where('name', $location)->exists();
        if (!$exists) throw new Exception('Требуется добавить локацию', 1);
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
