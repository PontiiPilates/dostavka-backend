<?php

namespace Database\Seeders\Tk;

use App\Enums\Vozovoz\VozovozUrlType;
use App\Models\Location;
use App\Models\Tk\TerminalVozovoz;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class TerminalVozovozSeeder extends Seeder
{
    private array $candidatsToUpdate = [];

    private string $url;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // особенность данной тк заключается в том, что нельзя получить сразу все терминалы
        // поэтому в данном сидере есть возможность получить файл из множества запросов
        // а затем посеять денные в таблицу из этого файла
        // однако сами данные достаточно чистые и должны находиться выше прочих

        $this->url = config('companies.vozovoz.url') . '?token=' . config('companies.vozovoz.token');

        // сохранение в файлы
        $this->createDataFiles(10000);

        // посев данных
        // $this->seeding();
    }

    /**
     * Производит засев базы данных на основе дата-файлов.
     */
    private function seeding()
    {
        $dataFiles = Storage::files('assets/tk/vozovoz/data-files');

        foreach ($dataFiles as $dataFile) {

            $terminals = Storage::json($dataFile);

            foreach ($terminals as $terminal) {

                $terminal = (object) $terminal;

                $location = Location::query()
                    ->where('name', $terminal->name)
                    ->whereHas('country', function ($query) use ($terminal) {
                        $query->where('alpha2', $terminal->country);
                    })->first();

                // если локация не обнаружена, то она попадает в список кандидатов на парсинг
                if (!$location) {
                    $this->candidatsToUpdate[] = $terminal->name . ': ' . $terminal->region_str;
                    continue;
                }

                TerminalVozovoz::updateOrCreate(
                    ['identifier' => $terminal->guid],
                    [
                        'location_id' => $location->id,
                        'identifier' => $terminal->guid,
                        'name' => $terminal->name,
                        'dirty' => $terminal->region_str,
                    ]
                );
            }
        }


        dump('Следующие локации остались не добавленными: ', $this->candidatsToUpdate);
    }

    /**
     * Сохраняет полученные данные в файлы c указанным количеством элементов.
     */
    private function createDataFiles($chunk)
    {
        $this->createProgressFile();

        do {
            $progress = Storage::json('assets/tk/vozovoz/progress.json');
            $progress = (object) $progress;

            $template = $this->template($progress->offset, $progress->limit);
            $response = Http::post($this->url, $template)->object();

            $progress->offset += $progress->limit;

            foreach ($response->response->data as $place) {
                $content[] = $place;
            }

            if ($progress->offset % $chunk == 0) {
                // сохранение чанка
                Storage::put("assets/tk/vozovoz/data-files/vozovoz_$progress->offset.json", json_encode($content, JSON_UNESCAPED_UNICODE));
                $content = [];
            }

            // сохранение прогресса
            Storage::put('assets/tk/vozovoz/progress.json', json_encode($progress));

            dump("Выполнено $progress->offset из $progress->total");
        } while ($progress->offset < $progress->total);

        // сохранение остатка
        Storage::put("assets/tk/vozovoz/data-files/vozovoz_$progress->offset.json", json_encode($content, JSON_UNESCAPED_UNICODE));
    }

    /**
     * Создает прогресс-файл, если он не обнаружен.
     */
    private function createProgressFile()
    {
        $progress = Storage::json('assets/tk/vozovoz/progress.json');

        if (!$progress) {
            $template = $this->template();
            $response = Http::post($this->url, $template)->object();
            Storage::put('assets/tk/vozovoz/progress.json', json_encode($response->response->meta));

            dump('Файл для фиксации прогресса загрузки данных создан');
        }
    }

    /**
     * Шаблон запроса.
     */
    private function template($offset = null, $limit = null): array
    {
        return [
            'object' => VozovozUrlType::Location->value,
            'action' => 'get',
            'params' => array_filter([
                'offset' => $offset,
                'limit' => $limit,
            ]),
        ];
    }
}
