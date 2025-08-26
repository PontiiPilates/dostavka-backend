<?php

namespace Database\Seeders\Tk;

use App\Enums\Boxberry\BoxberryUrlType;
use App\Models\Country;
use App\Models\Region;
use App\Models\Tk\TerminalBoxberry;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

class TerminalBoxberrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $url = config('companies.boxberry.url');
        $token = config('companies.boxberry.token');

        $response = Http::get($url, ['token' => $token, 'method' => BoxberryUrlType::ListCitiesFull->value]);

        $iterable = 0;
        $timeStart = Carbon::now();
        $count = count($response->object());

        foreach ($response->object() as $place) {

            $place = (object) $place;

            $country = Country::where('code', $place->CountryCode)->first()->alpha2;

            $region = $place->Region;
            $district = $place->District;
            $type = $place->Prefix;
            $federal = false;

            // если обнаружена принадлежность к территиории федерального значения
            if ($place->Name == 'Санкт-Петербург' || $place->Name == 'Москва' || $place->Name == 'Севастополь') {
                $region = $place->Name;
                $federal = true;
            }

            TerminalBoxberry::create([
                'identifier' => $place->Code,
                'name' => $place->Name,
                'type' => $this->correctorType($type),
                'district' => $district
                    ? $this->cleanDistrictName($district)
                    : null,
                'region' => $region
                    ? $this->cleanRegionName($region)
                    : null,
                'federal' => $federal,
                'country' => $country,
            ]);

            $iterable++;
        }

        $timeEnd = Carbon::now();
        $executionTime = $timeStart->diffInSeconds($timeEnd);

        $this->command->info("Добавлено $iterable локаций из $count за $executionTime сек.");
    }

    /**
     * Возвращает коректное имя типа.
     */
    private function correctorType(string $name): string
    {
        if ($name == 'г') {
            return 'город';
        }
        if ($name == 'рп') {
            return 'рабочий посёлок';
        }
        if ($name == 'п') {
            return 'посёлок';
        }
        if ($name == 'ст-ца') {
            return 'станица';
        }
        if ($name == 'д') {
            return 'деревня';
        }
        if ($name == 'дп') {
            return 'деревенский посёлок';
        }
        if ($name == 'с') {
            return 'село';
        }
        if ($name == 'х') {
            return 'хутор';
        }
        if ($name == 'мкр') {
            return 'микрорайон';
        }
        if ($name == 'нп') {
            return 'населённый пункт';
        }

        return $name;
    }

    /**
     * Возвращает чистое название района.
     */
    private function cleanDistrictName(string $name): string
    {
        return $name . ' район';
    }

    /**
     * Возвращает чистое название региона.
     */
    private function cleanRegionName(string $name): string
    {
        $badNames = $badNames = [
            'Саха /Якутия/',
            'Кемеровская область - Кузбасс',
        ];

        $goodNames = [
            'Республика Саха (Якутия)',
            'Кемеровская область',
        ];

        $name = str_replace($badNames, $goodNames, $name);

        return Region::where('name', 'like', "%$name%")->first()->name ?? $name;
    }
}
