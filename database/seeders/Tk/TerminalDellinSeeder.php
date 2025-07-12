<?php

namespace Database\Seeders\Tk;

use App\Enums\Dellin\DellinUrlType;
use App\Models\Location;
use App\Models\Tk\TerminalDellin;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class TerminalDellinSeeder extends Seeder
{
    private string $url;
    private string $token;
    private string $terminals;

    private array $candidatsToUpdate = [];


    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->url = config('companies.dellin.url');
        $this->token = config('companies.dellin.token');
        $this->terminals = 'assets/tk/dellin/terminals.json';

        if ($this->checkDiff()) {
            dump("Появились новые изменения, происходит обновление исходного файла");
            $this->updateOrSaveTerminals();
        }

        $this->addTerminals();
        dump('Следующие локации остались не добавленными: ', $this->candidatsToUpdate);
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

        dump("Новый файл терминалов транспортных компаний успешно загружен");
    }

    private function addTerminals(): void
    {
        $file = Storage::json($this->terminals);

        foreach ($file['city'] as $city) {
            $city = (object) $city;

            foreach ($city->terminals as $terminals) {

                foreach ($terminals as $terminal) {
                    $terminal = (object) $terminal;

                    // если терминал является основным для населённого пункта
                    // (дело в том, что терминалов может быть несколько)
                    // (атомарный уровень до выбора терминала пока еще не интересует)
                    // (сейчас актуален выбор на уровне населённого пункта)
                    if ($terminal->default) {

                        // todo: необходимо добавить связь с regions
                        // todo: дело в том, что список не содержить принадлежности к стране
                        // todo: поэтому следует добавить принадлежность к региону
                        // todo: для того, чтобы делать выборку по regions необходимо распарсить fullAddress
                        // todo: парсинг региональной принадлежности еще не готов
                        $location = Location::where('name', $city->name)->first();

                        // если локация не обнаружена, то она попадает в список кандидатов на парсинг
                        if (!$location) {
                            $this->candidatsToUpdate[] = $city->name . ': ' . $terminal->fullAddress;
                            continue;
                        }

                        TerminalDellin::create([
                            'location_id' => $location->id,
                            'terminal_id' => $terminal->id,
                            'city_id' => $city->code,
                            'name' => $city->name,
                            'dirty' => $terminal->fullAddress,
                        ]);
                    }
                }
            }
        }
    }
}
