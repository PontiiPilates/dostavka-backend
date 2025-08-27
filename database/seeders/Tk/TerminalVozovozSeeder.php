<?php

namespace Database\Seeders\Tk;

use App\Enums\CompanyType;
use App\Enums\EnvironmentType;
use App\Enums\LocationType;
use App\Models\Tk\TerminalVozovoz;
use App\Traits\Logger;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class TerminalVozovozSeeder extends Seeder
{
    use Logger;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // особенность данной тк в том, что нельзя получить сразу все терминалы
        // команда php artisan app:create-data-files-vozovoz обеспечивает сборку файлов данных из множества запросов
        // однако сами данные достаточно чистые и должны находиться выше прочих

        $this->terminals();
    }

    private function terminals()
    {
        $dataFiles = Storage::files('assets\geo\tk\vozovoz\data-files');

        $countFiles = 0;
        foreach ($dataFiles as $dataFile) {

            $terminals = Storage::json($dataFile);

            $iterable = 0;
            $timeStart = Carbon::now();

            foreach ($terminals as $terminal) {

                $terminal = (object) $terminal;

                // типы, которые нужно добавлять в базу данных
                // другие типы не представляют ценности
                // здесь формат данных как в исходном файле
                $allowedTypes = [
                    "с", // село
                    "д", // деревня
                    "х", // хутор
                    "п", // посёлок
                    "ст-ца", // станица
                    "аг", // агрогородок
                    "сп.", // сельское поселение
                    "пгт", // посёлок городского типа
                    "рп", // рабочий поселок
                    "с.", // село
                    "высел", // высел
                    "с/п", // сельское поселение
                    "аул", // аул
                    "сл", // слобода
                    "ост-в", // остров
                    "п.", // посёлок
                    "пгт.", // посёлок городского типа
                    "дп", // дачный посёлок
                    "гп", // городской посёлок
                    "г", // город
                ];

                // если локации нет в списке доступных, то следующая итерация
                if (!in_array($terminal->type, $allowedTypes)) {
                    continue;
                }

                $territories = explode(',', $terminal->region_str);

                $region = null;
                $district = null;
                $city = null;
                $federal = false;
                foreach ($territories as $key => $territory) {

                    // если есть явное указание на территиорию федерального значения
                    if (str_contains($territory, 'значения')) {
                        $region = $terminal->name;
                        $federal = true;
                        break;
                    }

                    // если территория принадлежит субъекту
                    if (
                        str_contains($territory, 'край')
                        || str_contains($territory, 'округ')
                        || str_contains($territory, 'респ')
                        || str_contains($territory, 'Респ')
                        || str_contains($territory, 'обл')
                        || str_contains($territory, 'АО')
                    ) {
                        $region = trim($territories[$key]);
                        break;
                    }

                    // если территория принадлежит району
                    if (str_contains($territory, 'р-н')) {
                        $district = trim($territories[$key]);
                    }

                    // если у территории нет принадлежности к:
                    // - городу федерального значения
                    // - субъекту
                    // - району,
                    // но есть принадлежность к городу
                    if (str_contains($territory, ' г')) {

                        $badCities = [
                            ' Московский г',
                            ' Зеленоград г',
                            ' Павловск г',
                            ' Ноокат г',
                            ' Узген г',
                            'Нарын г',
                            'Ош г',
                        ];
                        if (!in_array($territory, $badCities)) {
                            $city = trim($territories[$key]);
                        }
                    }
                }

                // если не удалось обнаружить принадлежность к региону, но существует принадлежность к городу
                if ($city && !$region) {
                    $region = $city;
                }

                // если не удалось обнаружить принадлежность (распарсить)
                if (!$region && !$district && !$city) {
                    $this->parseFail(CompanyType::Vozovoz->value, $terminal->name . ': ' . $terminal->region_str);
                }

                TerminalVozovoz::create([
                    'identifier' => $terminal->guid,
                    'name' => $terminal->name,
                    'type' => $this->typesReplacer($terminal->type),
                    'district' => $this->territoryReplacer($district),
                    'region' => $this->territoryReplacer($region),
                    'federal' => $federal,
                    'country' => $terminal->country,
                ]);

                $iterable++;
            }

            $timeEnd = Carbon::now();
            $executionTime = $timeStart->diffInSeconds($timeEnd);
            $executionTime = number_format((float) $executionTime, 1, '.');

            $this->command->info("Добавлено $iterable терминалов, $executionTime сек.");

            // если сидер выполняется в 'Dev', то выполняется обработка 1 файла из 32 (для скорости)
            if (config('app.env') == EnvironmentType::Dev->value) {
                break;
            }

            $countFiles++;
            $this->command->info("Обработано $countFiles из 32");
        }
    }

    /**
     * Преобразует тип населенного пункта.
     * 
     * Исходные данные содержат различные и не очевидные сокращения.
     * В результате работы метода, все эти данные приводятся к стандарту.
     */
    private function typesReplacer($type)
    {
        // очистка строки
        $type = str_replace('.', '', $type);

        // замена всей строки
        $badTypes = [
            1 => "с",
            2 => "д",
            3 => "х",
            4 => "п",
            5 => "ст-ца",
            6 => "аг",
            7 => "сп",
            8 => "рп",
            9 => "дп",
            10 => "с/п",
            11 => "сл",
            12 => "ост-в",
            13 => "гп",
            14 => "г",
        ];

        $goodTypes = [
            1 => LocationType::Village->value,
            2 => LocationType::Hamlet->value,
            3 => LocationType::Farmstead->value,
            4 => LocationType::Township->value,
            5 => LocationType::Stanitsa->value,
            6 => LocationType::AgroTown->value,
            7 => LocationType::RualVillage->value,
            8 => LocationType::JobVillage->value,
            9 => LocationType::CottageVillage->value,
            10 => LocationType::RualVillage->value,
            11 => LocationType::Sloboda->value,
            12 => LocationType::Island->value,
            13 => LocationType::UrbanVillage->value,
            14 => LocationType::Town->value,
        ];

        $key = array_search($type, $badTypes);
        if ($key >= 1) {
            $type = $goodTypes[$key];
        }

        return $type;
    }

    private function territoryReplacer($territory)
    {
        // замена вхождений строки
        $search = [
            1 => 'р-н',
            2 => 'обл',
            3 => 'Аобласть',
            4 => '.',
            5 => ' г',
        ];

        $replace = [
            1 => LocationType::District->value,
            2 => LocationType::Area->value,
            3 => LocationType::AutonomousRegion->value,
            4 => '',
            5 => '',
        ];

        $territory = str_replace($search, $replace, $territory);

        // замена всей строки
        $badTerritoryNames = [
            5 => "Адыгея Респ",
            6 => "Алтай Респ",
            7 => "Башкортостан Респ",
            8 => "Бурятия Респ",
            9 => "Дагестан Респ",
            1 => "Донецкая Народная респ",
            10 => "Ингушетия Респ",
            2 => "Кабардино-Балкарская Респ",
            11 => "Калмыкия Респ",
            3 => "Карачаево-Черкесская Респ",
            12 => "Карелия Респ",
            13 => "Коми Респ",
            14 => "Крым Респ",
            4 => "Луганская Народная респ",
            15 => "Марий Эл Респ",
            16 => "Мордовия Респ",
            17 => "Саха /Якутия/ Респ",
            18 => "Северная Осетия - Алания Респ",
            19 => "Татарстан Респ",
            20 => "Тыва Респ",
            22 => "Удмуртская Респ",
            21 => "Хакасия Респ",
            23 => "Чеченская Респ",
            24 => "Чувашская Республика Чувашия",
            25 => "Ямало-Ненецкий АО",
            26 => "Ненецкий АО",
            27 => "Ханты-Мансийский Автономный округ - Югра АО",
            28 => "Саха (Якутия) Респ",
            29 => "Чукотский АО",
            30 => "Донецк респ",
        ];

        $goodTerritoryNames = [
            1 => "Донецкая Народная Республика",
            2 => "Кабардино-Балкарская Республика",
            3 => "Карачаево-Черкесская Республика",
            4 => "Луганская Народная Республика",
            5 => "Республика Адыгея",
            6 => "Республика Алтай",
            7 => "Республика Башкортостан",
            8 => "Республика Бурятия",
            9 => "Республика Дагестан",
            10 => "Республика Ингушетия",
            11 => "Республика Калмыкия",
            12 => "Республика Карелия",
            13 => "Республика Коми",
            14 => "Республика Крым",
            15 => "Республика Марий Эл",
            16 => "Республика Мордовия",
            17 => "Республика Саха (Якутия)",
            18 => "Республика Северная Осетия - Алания",
            19 => "Республика Татарстан",
            20 => "Республика Тыва",
            21 => "Республика Хакасия",
            22 => "Удмуртская Республика",
            23 => "Чеченская Республика",
            24 => "Чувашская Республика - Чувашия",
            25 => "Ямало-Ненецкий автономный округ",
            26 => "Ямало-Ненецкий автономный округ",
            27 => "Ханты-Мансийский автономный округ - Югра",
            28 => "Республика Саха (Якутия)",
            29 => "Чукотский автономный округ",
            30 => "Донецкая Народная Республика",
        ];

        $key = array_search($territory, $badTerritoryNames);
        if ($key >= 1) {
            $territory = $goodTerritoryNames[$key];
        }

        return $territory;
    }
}
