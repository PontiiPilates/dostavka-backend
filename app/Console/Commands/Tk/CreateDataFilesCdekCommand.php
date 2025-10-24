<?php

namespace App\Console\Commands\Tk;

use App\Enums\Cdek\CdekUrlType;
use App\Services\Tk\TokenCdekService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class CreateDataFilesCdekCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-data-files-cdek';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    private $token = '';
    private $url = '';

    private $size = 1000; // количество элементов на странице
    private $limit = 999; // максимальное количество страниц

    private $progressFile = 'assets/geo/tk/cdek/progress.json';

    /**
     * Формирует файлы с данными о локациях для СДЕК. Сидер соответствующей тк использует эти файлы для наполнения базы даннах.
     */
    public function handle()
    {
        $tokenCdecService = new TokenCdekService();
        $this->token = $tokenCdecService->getActualToken();
        $this->url = $tokenCdecService->url . CdekUrlType::Cities->value;

        $this->createDataFiles();
    }

    /**
     * Сохраняет полученные данные в файлы c указанным количеством элементов.
     */
    private function createDataFiles()
    {
        $this->createProgressFile();

        $progress = Storage::json($this->progressFile);
        $progress = (object) $progress;

        // если прогресс соответствует лимиту
        if ($progress->page == $this->limit) {
            dump("Получение данных успешно завершено");
            return 0;
        }

        $timeStart = Carbon::now();

        // получать данные, пока прогресс не станет соответствовать заявленному лимиту
        for ($i = $progress->page; $i <= $this->limit; $i++) {

            $response = Http::withToken($this->token)->get($this->url, $this->parameters($progress->page));
            $response = $response->object();

            $fileNumber = $this->numerator($i);
            Storage::put("assets/geo/tk/cdek/data-files/cdek_$fileNumber.json", json_encode($response, JSON_UNESCAPED_UNICODE));

            $progress->page = $i;
            Storage::put($this->progressFile, json_encode($progress));

            $timeEnd = Carbon::now();
            $executionTime = $timeStart->diffInSeconds($timeEnd);
            $executionTime = number_format((float) $executionTime, 1, '.');

            dump("Выполнено $i из $this->limit, $executionTime сек.");
            return 0;
        }
    }

    /**
     * Создает прогресс-файл, если он не обнаружен.
     */
    private function createProgressFile()
    {
        $progress = Storage::json($this->progressFile);

        if (!$progress) {
            $template = $this->parameters();
            Storage::put($this->progressFile, json_encode(['download' => $template, 'seeding' => 0]));

            dump('Файл для фиксации прогресса загрузки данных создан');
            return 0;
        }
    }

    /**
     * Параметры запроса.
     */
    private function parameters($page = 0): array
    {
        return [
            'page' => $page,
            'size' => $this->size,
        ];
    }

    /**
     * Преобразует номер страницы в формат с ведущими нулями.
     */
    private function numerator($number)
    {
        switch (strlen($number)) {
            case 1:
                return "00$number";
            case 2:
                return "0$number";
            default:
                return $number;
        }
    }
}
