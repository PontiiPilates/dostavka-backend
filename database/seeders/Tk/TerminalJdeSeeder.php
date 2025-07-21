<?php

namespace Database\Seeders\Tk;

use App\Enums\Jde\JdeUrlType;
use App\Models\Location;
use App\Models\Tk\TerminalJde;
use Illuminate\Database\Seeder;
use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\Http;

class TerminalJdeSeeder extends Seeder
{
    private array $candidatsToUpdate = [];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $url = config('companies.jde.url') . JdeUrlType::Geo->value;

        $responses = $this->send($url);
        $this->save($responses);
    }

    /**
     * Запрос к API на выдачу списка доступных терминалов.
     * 
     * @param string $url
     * @return array
     */
    private function send(string $url): array
    {
        return Http::pool(fn(Pool $pool) => [
            $pool->as(1)->get($url, ['mode' => 1]),
            $pool->as(2)->get($url, ['mode' => 2]),
        ]);
    }

    /**
     * Обработка ответа, сохранение в базу.
     * 
     * @param array $responses
     * @return void
     */
    private function save(array $responses): void
    {
        foreach ($responses as $key => $response) {

            foreach ($response->object() as $terminal) {

                $location = Location::query()
                    ->where('name', $terminal->city)
                    ->whereHas('country', function ($query) use ($terminal) {
                        $query->where('name', $terminal->contry_name);
                    })->first();

                // если локация не обнаружена, то она попадает в список кандидатов на парсинг
                if (!$location) {
                    $this->candidatsToUpdate[] = $terminal->city . ': ' . $terminal->addr;
                    continue;
                }

                switch ($key) {
                    case 1:
                        TerminalJde::create([
                            'location_id' => $location->id,
                            'identifier' => $terminal->code,
                            'name' => $terminal->city,
                            'dirty' => $terminal->addr,
                            'acceptance' => true,
                        ]);
                        break;
                    case 2:
                        TerminalJde::updateOrCreate(
                            ['identifier' => $terminal->code],
                            [
                                'location_id' => $location->id,
                                'identifier' => $terminal->code,
                                'name' => $terminal->city,
                                'dirty' => $terminal->addr,
                                'issue' => true,
                            ]
                        );
                        break;
                }
            }
        }

        dump('Следующие локации остались не добавленными: ', $this->candidatsToUpdate);
    }
}
