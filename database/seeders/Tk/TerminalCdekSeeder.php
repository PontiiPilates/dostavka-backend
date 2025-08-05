<?php

namespace Database\Seeders\Tk;

use App\Enums\Cdek\CdekUrlType;
use App\Models\Country;
use App\Models\Location;
use App\Models\Region;
use App\Models\Tk\TerminalCdek;
use App\Services\Tk\TokenCdekService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;

class TerminalCdekSeeder extends Seeder
{
    private array $unfind = [];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // при всей порядочности, список не содержит типа населенного пункта
        // также выяснилось, что список регионов расходится со списком городов в части наименования и кодов регионов
        // очевидно, что список городов ограничен 1000 элементов, но в документации не описаны параметры смены страниц

        $tokenCdecService = new TokenCdekService();
        $token = $tokenCdecService->getActualToken();

        $response = Http::withToken($token)->get($tokenCdecService->url . CdekUrlType::Cities->value);

        $countLocation = 0;
        $countRegion = 0;
        $countTerminal = 0;

        foreach ($response->object() as $city) {

            // обработка ситуации, когда отсутствует регион в данных от СДЕК
            if (!isset($city->region)) {
                continue;
            }

            $country = Country::where('alpha2', $city->country_code)->first();

            // корректировка наименования региона
            $correctionList = $this->correctionList();
            if (array_key_exists($city->region, $correctionList)) {
                $city->region = $correctionList[$city->region];
            }

            $region = Region::where('name', $city->region)->first();

            // если регион не обнаружен, то происходит его добавление
            if (!$region) {
                $this->unfind[] = "$city->country, регион: $city->region";
                $region = $this->createRegion($country, $city->region);
                $countRegion++;
            }

            $location = Location::query()
                ->where('name', $city->city)
                ->whereHas('region', function ($query) use ($city) {
                    $query->where('name', $city->region);
                })
                ->whereHas('country', function ($query) use ($city) {
                    $query->where('alpha2', $city->country_code);
                })->first();

            // если локация не обнаружена, то происходит ее добавление и добавление терминала
            if (!$location) {
                $location = $this->createLocation($country, $region, $city);
                $this->createTerminal($city, $location);

                $countLocation++;
                $countTerminal++;

                continue;
            }

            // если локация и регион обнаружены, то проиисходит просто добавление терминала
            $this->createTerminal($city, $location);

            $countTerminal++;
        }

        // в рамках метода cities происходит:
        // добавление регионов
        // добавление локаций
        // добавление терминалов

        dump("Добавлено $countRegion новых регионов");
        dump("Добавлено $countLocation новых населенных пунктов");
        dump("Добавлено $countTerminal терминалов");
    }

    private function correctionList(): array
    {
        return [
            'Удмуртия' => 'Удмуртская Республика',
            'Дагестан' => 'Республика Дагестан',
            'Мордовия' => 'Республика Мордовия',
            'Татарстан' => 'Республика Татарстан',
            'Марий Эл' => 'Республика Марий Эл',
            'Кабардино-Балкария' => 'Кабардино-Балкарская Республика',
        ];
    }

    private function createLocation($country, $region, $city): Location
    {
        return Location::create(
            [
                'country_id' => $country->id,
                'region_id' => $region->id,
                'name' => $city->city,
            ]
        );
    }

    private function createTerminal($city, $location): void
    {
        TerminalCdek::create(
            [
                'location_id' => $location->id,
                'identifier' => $city->code,
                'name' => $city->city,
                'dirty' => $city->city . ': ' . ($city->region ?? '') . ', ' . ($city->sub_region ?? ''),
            ]
        );
    }


    private function createRegion($country, $regionName): Region
    {
        return Region::create(
            [
                'country_id' => $country->id,
                'name' => $regionName,
            ]
        );
    }
}
