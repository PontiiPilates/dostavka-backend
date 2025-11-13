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
        $this->createProgressFile();

        $this->createDataFiles(1000);
    }

    /**
     * Сохраняет полученные данные в файлы c указанным количеством элементов.
     */
    private function createDataFiles($chunk)
    {
        $this->createProgressFile();

        do {
            $progress = Storage::json('assets/geo/tk/vozovoz/progress.json');
            $progress = json_decode(json_encode($progress));

            $parameters = $this->parameters($progress->download->offset, $progress->download->limit);
            $response = Http::post($this->url, $parameters)->object();

            $progress->download->offset += $progress->download->limit;

            foreach ($response->response->data as $place) {
                $content[] = $place;
            }

            // если смещение делится без остатка на размер чанка, значит это не последняя итерация
            if ($progress->download->offset % $chunk == 0) {
                // сохранение чанка
                Storage::put("assets/geo/tk/vozovoz/data-files/vozovoz_{$progress->download->offset}.json", json_encode($content, JSON_UNESCAPED_UNICODE));
                $content = [];
            }

            // сохранение прогресса
            Storage::put('assets/geo/tk/vozovoz/progress.json', json_encode($progress));

            $this->info("Выполнено {$progress->download->offset} из {$progress->download->total}");
        } while ($progress->download->offset < $progress->download->total);

        // сохранение остатка
        Storage::put("assets/geo/tk/vozovoz/data-files/vozovoz_{$progress->download->offset}.json", json_encode($content, JSON_UNESCAPED_UNICODE));
    }

    /**
     * Создает прогресс-файл, если он не обнаружен.
     */
    private function createProgressFile()
    {
        $progress = Storage::json('assets/geo/tk/vozovoz/progress.json');

        if (!$progress) {
            $parameters = $this->parameters();
            $response = Http::post($this->url, $parameters)->object();
            $template = $this->template($response->response->meta);
            Storage::put('assets/geo/tk/vozovoz/progress.json', json_encode($template));

            $this->info('Создан файл для фиксации прогресса загрузки данных');
        }
    }

    /**
     * Шаблон для progress-файла.
     */
    private function template($parameters)
    {
        return [
            'download' => $parameters,
            'seeding' => null,
        ];
    }

    /**
     * Шаблон запроса.
     */
    private function parameters($offset = null, $limit = null): array
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
