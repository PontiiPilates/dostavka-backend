<?php

namespace App\Console\Commands\Tk;

use App\Enums\Vozovoz\VozovozUrlType;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class CreateDataFilesVozovozCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-data-files-vozovoz';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Url to API
     * 
     * @var string
     */
    private $url = '';

    /**
     * Формирует файлы с данными о локациях для Возовоз. Сидер соответствующей тк использует эти файлы для наполнения базы даннах.
     */
    public function handle()
    {
        $this->url = config('companies.vozovoz.url') . '?token=' . config('companies.vozovoz.token');

        $this->createDataFiles(10000);
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
