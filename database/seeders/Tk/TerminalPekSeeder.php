<?php

namespace Database\Seeders\Tk;

use App\Enums\Pek\PekUrlType;
use App\Models\Tk\TerminalPek;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;

class TerminalPekSeeder extends Seeder
{
    private array $unknown = [];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $url = config('companies.pek.url');
        $user = config('companies.pek.user');
        $password = config('companies.pek.password');

        $response = Http::withBasicAuth($user, $password)->post($url . PekUrlType::Terminals->value);

        $iterable = 0;
        $timeStart = Carbon::now();
        foreach ($response->object()->branches as $location) {

            $countryCode = null;
            $region = null;
            $district = null;
            $federal = false;
            $onlyCity = false;
            $type = 'город';

            $countrys = [
                "BY" => "БЕЛАРУСЬ",
                "RU" => "РОССИЯ",
                "KZ" => "КАЗАХСТАН",
                "KZ" => "КАЗАХСТАН",
                "TM" => "ТУРКМЕНИЯ",
                "TH" => "ТАИЛАНД",
                "KG" => "КИРГИЗИЯ",
                "TJ" => "ТАДЖИКИСТАН",
                "AM" => "АРМЕНИЯ",
                "TR" => "ТУРЦИЯ",
                "UZ" => "УЗБЕКИСТАН",
            ];

            // определение принадлежности к стране
            $countryCode = array_search($location->country, $countrys);

            foreach ($location->cities as $city) {

                // если обнаружена принадлежность к району
                if (str_contains($city->title, 'р-н') || str_contains($city->title, 'район')) {
                    $district = trim($city->title);
                }

                $territories = explode(',', $location->address);

                foreach ($territories as $key => $territory) {

                    // если обнаружена принадлежность к территиории федерального значения
                    if ($city->title == 'Санкт-Петербург' || $city->title == 'Москва' || $city->title == 'Севастополь') {
                        $region = $city->title;
                        $federal = true;
                        break;
                    }

                    if (
                        str_contains($territory, 'край')
                        || str_contains($territory, 'Респ')
                        || str_contains($territory, 'обл')
                        || str_contains($territory, 'АО')
                    ) {
                        $region = trim($territories[$key], ' .');
                        break;
                    }

                    // если у территории нет принадлежности к:
                    // - городу федерального значения
                    // - субъекту
                    // - району,
                    // но есть принадлежность к городу
                    if (str_contains($territory, 'г')) {
                        $onlyCity = true;
                        break;
                    }
                }

                // исключительные случаи обработки территорий
                if ($city->title == 'Безопасное') {
                    $region = 'Ставропольский край';
                    $type = 'село';
                }

                if ($city->title == 'Ставрополь') {
                    $region = 'Ставропольский край';
                }

                if ($city->title == 'Старомарьевка') {
                    $region = 'Ставропольский край';
                    $type = 'село';
                }

                if ($city->title == 'Бангкок') {
                    $onlyCity = true;
                    $countryCode = 'SG';
                }

                if (
                    $city->title == 'Михайловка (от 1,5 тонн,7 кубов и 3 метров) (Уфимский р-н)'
                    || $city->title == 'Николаевка (от 1,5 тонн,7 кубов и 3 метров) (Уфимский р-н)'
                    || $city->title == 'Нурлино (от 1,5 тонн,7 кубов и 3 метров) (Уфимский р-н)'
                    || $city->title == 'Дмитриевка (от 1,5 тонн,7 кубов и 3 метров) (Уфимский р-н)'
                ) {
                    $type = 'село';
                    $district = '(Уфимский р-н)';
                }

                if (
                    $city->title == 'Быково (от 1,5 тонн,7 кубов и 3 метров) (Раменский р-н)'
                ) {
                    $type = 'село';
                    $district = '(Раменский р-н)';
                }

                if (!$region && !$district && !$onlyCity) {
                    $this->unknown[] = $city->title . ': ' . $location->address;
                }

                foreach ($location->divisions as $division) {

                    if ($division->cityId === $city->cityId) {

                        foreach ($division->warehouses as $warehouse) {

                            TerminalPek::create([
                                'identifier' => $warehouse->id,
                                'name' => $this->cleanCityName($city->title),
                                'type' => $type,
                                'district' => $district
                                    ? $this->cleanDistrictName($district)
                                    : null,
                                'region' => $region
                                    ? $this->cleanRegionName($region)
                                    : null,
                                'federal' => $federal,
                                'country' => $countryCode,
                                'max_weight' => $warehouse->maxWeight,
                                'max_volume' => $warehouse->maxVolume,
                                'max_weight_per_place' => $warehouse->maxWeightPerPlace,
                                'max_dimension' => $warehouse->maxDimension,
                            ]);

                            $iterable++;
                        }
                    }
                }
            }
        }

        $timeEnd = Carbon::now();
        $executionTime = $timeStart->diffInSeconds($timeEnd);

        dump("Добавлено $iterable новых терминалов. $executionTime сек.");

        if ($this->unknown) {
            dump('Появились территории, которые не удалось распарсить: ', $this->unknown);
        }
    }

    /**
     * Возвращает чистое название населённого пункта.
     */
    private function cleanCityName(string $name): string
    {
        $withouthRegion = strstr($name, '(', true);

        if ($withouthRegion) {
            return trim($withouthRegion);
        }

        return trim($name);
    }

    /**
     * Возвращает чистое название района.
     */
    private function cleanDistrictName(string $name): string
    {
        // замена всей строки
        $badDistrictName = [
            1 => "Сафоново (Смоленская обл.) (Сафоновский р-н)",
            2 => "Октябрьский (МО, Люберецкий р-н) (Люберецкий р-н)",
            3 => "Красногорск (Московская обл.) (Красногорский р-н)",
        ];

        $goodDistrictName = [
            1 => "Сафоновский район",
            2 => "Люберецкий район",
            3 => "Красногорский район",
        ];

        $key = array_search($name, $badDistrictName);
        if ($key >= 1) {
            return $goodDistrictName[$key];
        }

        // замена нежелательных вхождений
        $onlyCorrectDistrictName = strstr($name, '(');
        $onlyCorrectDistrictName = trim($onlyCorrectDistrictName, ' ().');
        $onlyCorrectDistrictName = str_replace(['р-н'], ['район'], $onlyCorrectDistrictName);

        return $onlyCorrectDistrictName;
    }

    /**
     * Возвращает чистое название региона.
     */
    private function cleanRegionName(string $name): string|null
    {
        // замена нежелательных вхождений
        $onlyCorrectRegionName = strstr($name, 'г.', true);

        if (!$onlyCorrectRegionName) {
            $onlyCorrectRegionName = $name;
        }

        if (str_ends_with($onlyCorrectRegionName, 'обл') || str_ends_with($onlyCorrectRegionName, 'обл.') || str_ends_with($onlyCorrectRegionName, 'обл..')) {
            $onlyCorrectRegionName = str_replace(['обл', 'обл.'], ['область', 'область'], $onlyCorrectRegionName);
        }

        if (str_ends_with($onlyCorrectRegionName, 'АО') || str_ends_with($onlyCorrectRegionName, 'АО.')) {
            $onlyCorrectRegionName = str_replace(['АО'], ['автономный округ'], $onlyCorrectRegionName);
        }

        $onlyCorrectRegionName = str_replace(['663300', '353960'], ['', ''], $onlyCorrectRegionName);

        $onlyCorrectRegionName = trim($onlyCorrectRegionName, ' .');

        // замена всей строки
        $badDistrictName = [
            1 => "Республика Саха",
            2 => "Ямало-Ненецкий АО",
            3 => "Ханты-Мансийский автономный округ",
            4 => "Кабардино-Балкарская Респ",
            5 => "Магаданская обл",
            6 => "Марий Эл Респ",
            7 => "Республика Северная Осетия – Алания",
            8 => "Республика Туркменистан Ашхабад  проспект Б. Туркменистан",
            9 => "Республика Северная Осетия-Алания",
            10 => "Респ. Казахстан",
            12 => "Чувашская Республика-Чувашия",
            13 => "Республика Узбекистан",
            14 => "Республика Турция",
            15 => "Башкортостан Респ",
            16 => "Республика Казахстан",
        ];

        $goodDistrictName = [
            1 => "Республика Саха (Якутия)",
            2 => "Ямало-Ненецкий автономный округ",
            3 => "Ханты-Мансийский автономный округ - Югра",
            4 => "Кабардино-Балкарская Республика",
            5 => "Магаданская область",
            6 => "Республика Марий Эл",
            7 => "Республика Северная Осетия-Алания",
            8 => null,
            9 => "Республика Северная Осетия-Алания",
            10 => null,
            12 => "Чувашская Республика - Чувашия",
            13 => null,
            14 => null,
            15 => "Республика Башкортостан",
            16 => null,
        ];

        $key = array_search($onlyCorrectRegionName, $badDistrictName);
        if ($key >= 1) {
            return $goodDistrictName[$key];
        }

        return $onlyCorrectRegionName;
    }
}
