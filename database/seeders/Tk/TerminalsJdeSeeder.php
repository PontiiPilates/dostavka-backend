<?php

namespace Database\Seeders\Tk;

use App\Enums\Jde\JdeUrlType;
use App\Models\City;
use App\Models\Country;
use App\Models\Tk\TerminalJde;
use Illuminate\Database\Seeder;
use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\Http;

class TerminalsJdeSeeder extends Seeder
{
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

                $countryName = $terminal->contry_name;
                $cityName = $terminal->city;
                $terminalId = $terminal->code;

                $country = Country::where('name', $countryName)->first();

                $city = City::updateOrCreate([
                    'city_name' => $cityName,
                    'country_id' => $country->id
                ]);

                switch ($key) {
                    case 1:
                        TerminalJde::create([
                            'city_id' => $city->id,
                            'city_name' => $cityName,
                            'terminal_id' => $terminalId,
                            'acceptance' => true,
                        ]);
                        break;
                    case 2:
                        TerminalJde::updateOrCreate(
                            ['terminal_id' => $terminalId],
                            [
                                'city_id' => $city->id,
                                'city_name' => $cityName,
                                'terminal_id' => $terminalId,
                                'issue' => true,
                            ]
                        );
                        break;
                }
            }
        }
    }
}
