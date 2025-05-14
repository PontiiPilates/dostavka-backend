<?php

namespace App\Console\Commands;

use App\Enums\Dellin\DellinUrlType;
use App\Models\City;
use App\Services\Clients\Tk\PostRest;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class AddFillFromDellinCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fill-dellin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Добавляет идентификаторы городов и терминалов для Дуловых линий';

    private string $url;
    private string $token;

    /**
     * Execute the console command.
     * todo: после завершения всех интеграций необходимо отрефакторить структуру базы данных
     * todo: а также производить заполнения в отдельные таблицы транспортных компаний
     * todo: разработать оркестрацию выполнения данных команд, например bash
     */
    public function handle()
    {
        $this->url = config('companies.dellin.url');
        $this->token = config('companies.dellin.token');

        $check = $this->checkDiff();

        if ($check) {
            $this->line("Появились новые изменения, происходит обновление исходного файла");
            $this->updateOrSaveTerminals();
            $this->line("Обновление записей в базе данных");
            $this->updateEntries();
        }

        return 0;
    }

    /**
     * Возвращает false, если изменения не обнаружены и true, если необходимо обновить файл.
     * 
     * @return bool
     */
    private function checkDiff(): bool
    {
        $file = Storage::get("/Tk/Dellin/terminals.json");
        $hash = hash('sha256', $file);

        $parameters = ['appkey' => $this->token];

        $client = new PostRest();
        $res = $client->send($this->url . DellinUrlType::Terminals->value, $parameters);

        if ($res->hash === $hash) {
            return false;
        } else {
            return true;
        }
    }

    private function updateOrSaveTerminals(): void
    {
        $parameters = ['appkey' => $this->token];

        $client = new PostRest();
        $res = $client->send($this->url . DellinUrlType::Terminals->value, $parameters);

        Storage::put("/Tk/Dellin/terminals.json", file_get_contents($res->url));

        $this->line("Новый файл терминалов транспортных компаний успешно загружен");
    }

    private function updateEntries(): void
    {

        $file = Storage::json("/Tk/Dellin/terminals.json");

        foreach ($file['city'] as $key => $city) {

            $cityName = $city['name'];
            $cityCode = $city['code'];

            foreach ($city['terminals'] as $key => $terminals) {

                foreach ($terminals as $key => $terminal) {

                    $terminalDefault = $terminal['default'];

                    if ($terminalDefault) {

                        $terminalId = $terminal['id'];

                        $update = City::where('city_name', $cityName)->update([
                            "city_code_dellin" => $cityCode,
                            "terminal_id_dellin" => $terminalId,
                        ]);

                        if (!$update) {
                            $this->line("Не удалось обновить для: $cityName: $cityCode: $terminalId");
                        }
                    }
                }
            }
        }
    }
}
