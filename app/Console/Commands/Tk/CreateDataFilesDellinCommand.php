<?php

namespace App\Console\Commands\Tk;

use App\Enums\Dellin\DellinUrlType;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class CreateDataFilesDellinCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-data-files-dellin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    private string $url;
    private string $token;
    private string $terminals;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->url = config('companies.dellin.url');
        $this->token = config('companies.dellin.token');
        $this->terminals = 'assets/geo/tk/dellin/terminals.json';

        // если есть хэш файла изменился
        if ($this->checkDiff()) {
            $this->line("Появились новые изменения, происходит обновление исходного файла");
            $this->updateOrSaveTerminals();
        } else {
            $this->info("Исходный файл в актуальном состоянии");
        }
    }

    /**
     * Возвращает false, если изменения не обнаружены и true, если необходимо обновить файл.
     * 
     * @return bool
     */
    private function checkDiff(): bool
    {
        $file = Storage::get($this->terminals);
        $hash = hash('sha256', $file);

        $parameters = ['appkey' => $this->token];

        $response = Http::post($this->url . DellinUrlType::Terminals->value, $parameters);
        $response = $response->object();

        if ($response->hash === $hash) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Обновляет файл с терминалами компании.
     * 
     * @return void
     */
    private function updateOrSaveTerminals(): void
    {
        $parameters = ['appkey' => $this->token];

        $response = Http::post($this->url . DellinUrlType::Terminals->value, $parameters);
        $response = $response->object();

        Storage::put($this->terminals, file_get_contents($response->url));

        $this->info("Новый файл терминалов транспортных компаний успешно загружен");
    }
}
