<?php

namespace Database\Seeders\Tk;

use App\Enums\CompanyType;
use App\Enums\Jde\JdeUrlType;
use App\Enums\LocationType;
use App\Models\Location;
use App\Models\Tk\TerminalJde;
use App\Traits\Logger;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\Http;

class TerminalJdeSeeder extends Seeder
{
    use Logger;

    /**
     * Принцип парсинга:
     * поскольку локаций немного и список не образующий,
     * то имеет смысл проверить наличие локаций в базе,
     * они наверняка уже есть из более качественных списков,
     * но предпочтительно знать,
     * какие локации небыли обнаружены среди элементов текущего списка,
     * они попадут в лог парсинга,
     * локации из текущего списка достаточно лишь зарегистрировать,
     * остаток парсинга небольшой, подходит для ручной полуавтоматизации,
     * этот список дополняет локации вначале выполнения,
     * чтобы позже сидер мог обратиться к ним,
     * для некоторых наименований применяется полная замена строки перед проверкой в базе локаций,
     */
    public function run(): void
    {
        $url = config('companies.jde.url') . JdeUrlType::Geo->value;

        $responses = Http::pool(fn(Pool $pool) => [
            $pool->as(1)->get($url, ['mode' => 1]), // пункты приёма
            $pool->as(2)->get($url, ['mode' => 2]), // пункты выдачи
        ]);

        TerminalJde::truncate();

        $this->addLocation();

        $iterable = 0;
        $timeStart = Carbon::now();

        foreach ($responses as $key => $response) {

            foreach ($response->object() as $terminal) {

                $region = null;
                $federal = false;

                // преобразование нежелательных названий
                $terminal->city = $this->normalizeName($terminal->city);

                // если есть принадлежность к городу федерального значения
                if ($terminal->city == 'Санкт-Петербург' || $terminal->city == 'Москва' || $terminal->city == 'Севастополь') {
                    $region = $terminal->city;
                    $federal = true;
                }

                $location = Location::where('name', $this->normalizeName($terminal->city))
                    ->whereHas('country', function ($query) use ($terminal) {
                        $query->where('name', $terminal->contry_name);
                    })->first();

                // если локация не обнаружена, то она попадает в список кандидатов на парсинг
                if (!$location) {
                    $this->parseFail(CompanyType::Jde->value, $terminal->city . ': ' . $terminal->addr);
                    continue;
                }

                switch ($key) {
                    case 1:
                        TerminalJde::updateOrCreate(
                            ['identifier' => $terminal->code],
                            [
                                'location_id' => $location->id,
                                'identifier' => $terminal->code,
                                'name' => $terminal->city,
                                'region' => $location->region,
                                'federal' => $federal,
                                'acceptance' => true, // приём
                            ]
                        );
                        break;
                    case 2:
                        TerminalJde::updateOrCreate(
                            ['identifier' => $terminal->code],
                            [
                                'location_id' => $location->id,
                                'identifier' => $terminal->code,
                                'name' => $terminal->city,
                                'region' => $location->region,
                                'federal' => $federal,
                                'issue' => true, // выдача
                            ]
                        );
                        break;
                }

                // todo: имеет смысл проверять операцию добавления и инкрементировать после ее совершения
                $iterable++;
            }
        }

        $timeEnd = Carbon::now();
        $executionTime = $timeStart->diffInSeconds($timeEnd);
        $executionTime = number_format((float) $executionTime, 1, '.');

        $this->command->info("Добавлено $iterable терминалов, $executionTime сек.");
    }

    /**
     * Нормализация наименования населённого пункта
     */
    private function normalizeName($name)
    {
        $list = explode(',', $name);
        return trim($list[0]);
    }

    /**
     * Последний рубеж парсинга - вручную сформированный список
     */
    private function addLocation()
    {
        $additionals = [
            [
                'country_id' => 169,
                'region_id' => 83,
                'district_id' => null,
                'name' => 'Лабытнанги',
                'type' => LocationType::Town->value,
            ],
            [
                'country_id' => 169,
                'region_id' => 43,
                'district_id' => null,
                'name' => 'Малмыж',
                'type' => LocationType::Town->value,
            ],
            [
                'country_id' => 169,
                'region_id' => 42,
                'district_id' => null,
                'name' => 'Яя',
                'type' => LocationType::Pgt->value,
            ],
            [
                'country_id' => 169,
                'region_id' => 75,
                'district_id' => null,
                'name' => 'Борзя',
                'type' => LocationType::Town->value,
            ],
            [
                'country_id' => 169,
                'region_id' => 55,
                'district_id' => null,
                'name' => 'Тара',
                'type' => LocationType::Town->value,
            ],
            [
                'country_id' => 169,
                'region_id' => 11,
                'district_id' => null,
                'name' => 'Инта',
                'type' => LocationType::Town->value,
            ],
            [
                'country_id' => 169,
                'region_id' => 83,
                'district_id' => null,
                'name' => 'Нарьян-Мар',
                'type' => LocationType::Town->value,
            ],
            [
                'country_id' => 169,
                'region_id' => 75,
                'district_id' => null,
                'name' => 'Новая Чара',
                'type' => LocationType::Pgt->value,
            ],
            [
                'country_id' => 169,
                'region_id' => 14,
                'district_id' => null,
                'name' => 'Айхал',
                'type' => LocationType::Pgt->value,
            ],
            [
                'country_id' => 169,
                'region_id' => 14,
                'district_id' => null,
                'name' => 'Удачный',
                'type' => LocationType::Town->value,
            ],
            [
                'country_id' => 169,
                'region_id' => 14,
                'district_id' => null,
                'name' => 'Витим',
                'type' => LocationType::Pgt->value,
            ],
            [
                'country_id' => 169,
                'region_id' => 14,
                'district_id' => null,
                'name' => 'Пеледуй',
                'type' => LocationType::Pgt->value,
            ],
            [
                'country_id' => 169,
                'region_id' => 14,
                'district_id' => null,
                'name' => 'Мирный',
                'type' => LocationType::Town->value,
            ],
            [
                'country_id' => 169,
                'region_id' => 82,
                'district_id' => null,
                'name' => 'Анадырь',
                'type' => LocationType::Town->value,
            ],


        ];

        foreach ($additionals as $location) {
            $location = (object) $location;

            Location::updateOrCreate(
                [
                    'country_id' => $location->country_id,
                    'region_id' => $location->region_id ?? null,
                    'name' => $location->name,
                ],
                [
                    'country_id' => $location->country_id,
                    'region_id' => $location->region_id ?? null,
                    'district_id' => $location->district_id ?? null,
                    'name' => $location->name,
                    'type' => $location->type,
                ]
            );
        }
    }
}
