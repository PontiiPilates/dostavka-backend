<?php

namespace Database\Seeders\Tk;

use App\Models\Location;
use App\Models\Region;
use App\Models\Tk\TerminalCdek;
use App\Traits\Json;
use App\Traits\Utilits;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class TerminalCdekSeeder extends Seeder
{
    use Utilits, Json;

    private array $saveIncorrectRegion = [];
    private array $saveWithouthRegion = [];
    private array $saveWithouthCountry = [];

    private array $wrongRegionNames = [];
    private array $rightRegionNames = [];

    private $progressFile = 'assets/geo/tk/cdek/progress.json';

    private $currentFile = 0;


    /**
     * Особенности:
     * не содержит типа населённого пункта,
     * нельзя получить список сразу всех населенных пунктов,
     * команда php artisan app:create-data-files-cdek обеспечивает сборку файлов данных из множества запросов,
     * данный парсер работает в режиме регистрации идентификаторов локаций,
     * и не дополняет базу собственными локациями,
     */
    public function run(): void
    {
        $dataFiles = Storage::files('assets/geo/tk/cdek/data-files');

        // если data-файлы не существуют
        if (count($dataFiles) == 0) {
            $this->command->warn('Сначала необходимо создать data-файлы: docker-compose exec app php artisan app:create-data-files-cdek');
            return;
        }

        // получение данных о процессе наполнения
        $progress = Storage::get($this->progressFile);
        $progress = $this->toObject($progress);

        // если засев начинается с нуля, то очистка таблицы
        if ($progress->seeding === 0) {
            TerminalCdek::truncate();
        }

        $countFiles = 0;
        $countDataFiles = count($dataFiles);
        for ($i = $progress->seeding; $i <= $progress->download->page; $i++) {

            $page = $this->numerator($i);
            $locations = Storage::json("assets/geo/tk/cdek/data-files/cdek_$page.json");

            $iterable = 0;
            $timeStart = Carbon::now();

            foreach ($locations as $location) {
                $location = (object) $location;

                $federal = false;
                $region = null;

                // некоторые элементы могут не содержать принадлежность к региону
                if (!isset($location->country_code)) {
                    $this->saveWithouthCountry[] = $location->code;
                    continue;
                }

                // если не россия и не ближнее зарубежье
                if (!in_array($location->country_code, ["RU", "KZ", "KG", "BY", "AM"])) {
                    continue;
                }

                // если обнаружена принадлежность к территиории федерального значения
                if ($location->city == 'Санкт-Петербург' || $location->city == 'Москва' || $location->city == 'Севастополь') {
                    $region = $location->city;
                    $federal = true;
                }

                // некоторые элементы могут не содержать принадлежность к региону
                if (!isset($location->region)) {
                    $this->saveIncorrectRegion[] = $location->city;
                    continue;
                }

                // нежелательные регионы
                if ($location->region === 'Фиктивный') {
                    continue;
                }

                $regionName = $this->regionCorrector($location->region);
                $regionModel = Region::where('name', $regionName)->first();

                if ($regionModel) {
                    $region = $regionModel->name;
                } else {
                    $region = $location->region;
                    $this->saveIncorrectRegion[] = $location->region;
                }

                // режим пропуска, если локации не существует

                // $exists = Location::query()
                //     ->where('name', $location->city)
                //     ->when($region, function ($q) use ($region) {
                //         $q->with(['region' => function ($q) use ($region) {
                //             $q->where('name', $region);
                //         }]);
                //     })
                //     ->with(['country' => function ($q) use ($location) {
                //         $q->where('alpha2', $location->country_code);
                //     }])
                //     ->exists();

                // if (!$exists) {
                //     continue;
                // }

                // данные содержат повторяющиеся локации
                TerminalCdek::updateOrCreate(
                    [
                        'identifier' => $location->code,
                    ],
                    [
                        'identifier' => $location->code,
                        'name' => $location->city,
                        'region' => $region,
                        'federal' => $federal,
                        'country' => $location->country_code
                    ]
                );

                $iterable++;
            }

            $countFiles++;

            $timeEnd = Carbon::now();
            $executionTime = $timeStart->diffInSeconds($timeEnd);
            $executionTime = number_format((float) $executionTime, 1, '.');

            $this->command->info("Добавлено $iterable терминалов, $countFiles/$countDataFiles $executionTime сек.");

            // сохранение прогресса о наполнении базы данными
            $progress->seeding = $page;
            Storage::put($this->progressFile, json_encode($progress));
        }

        // автоматическое обнуление прогресса после успешного засева всех файлов
        $progress->seeding = '0';
        Storage::put($this->progressFile, json_encode($progress));
    }

    /**
     * Корректировка наименования региона.
     */
    private function regionCorrector($regionName): string
    {
        $regionName = str_replace(['город '], '', $regionName);

        $regionName = trim($regionName);

        $wrongRegionNames = [
            1 => 'Марий Эл',
            2 => 'Татарстан',
            3 => 'Калмыкия',
            4 => 'Удмуртия',
            5 => 'Кемеровская область - Кузбасс',
            6 => 'Адыгея',
            7 => 'Мордовия',
            8 => 'Дагестан',
            9 => 'Ингушетия',
            10 => 'Кабардино-Балкария',
            11 => 'Карачаево-Черкесия',
        ];

        $rightRegionNames = [
            1 => 'Республика Марий Эл',
            2 => 'Республика Татарстан',
            3 => 'Республика Калмыкия',
            4 => 'Удмуртская Республика',
            5 => 'Кемеровская область',
            6 => 'Республика Адыгея',
            7 => 'Республика Мордовия',
            8 => 'Республика Дагестан',
            9 => 'Республика Ингушетия',
            10 => 'Кабардино-Балкарская Республика',
            11 => 'Карачаево-Черкесская Республика',
        ];

        $key = array_search($regionName, $wrongRegionNames);
        if ($key >= 1) {
            return $rightRegionNames[$key];
        } else {
            return $regionName;
        }
    }

    private function unique()
    {
        return array_unique([]);
    }
}
