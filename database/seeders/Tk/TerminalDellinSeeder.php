<?php

namespace Database\Seeders\Tk;

use App\Models\Tk\TerminalDellin;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TerminalDellinSeeder extends Seeder
{
    private string $terminals;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->terminals = 'assets/geo/tk/dellin/terminals.json';

        $file = Storage::json($this->terminals);

        $iterable = 0;
        $timeStart = Carbon::now();
        foreach ($file['city'] as $city) {
            $city = (object) $city;

            foreach ($city->terminals as $terminals) {

                foreach ($terminals as $terminal) {
                    $terminal = (object) $terminal;

                    // если терминал является основным для населённого пункта
                    if ($terminal->default) {

                        $territories = explode(',', $terminal->fullAddress);

                        $region = null;
                        $district = null;
                        $federal = false;
                        foreach ($territories as $key => $territory) {

                            // если обнаружена принадлежность к территиории федерального значения
                            if ($city->name == 'Санкт-Петербург' || $city->name == 'Москва' || $city->name == 'Севастополь') {
                                $region = $city->name;
                                $federal = true;
                                break;
                            }

                            if (
                                str_contains($territory, 'край')
                                || str_contains($territory, 'Респ')
                                || str_contains($territory, 'обл')
                                || str_contains($territory, 'АО')
                            ) {
                                $region = trim($territories[$key], ' .');
                            }

                            // если территория принадлежит району
                            if (str_contains($territory, 'р-н')) {
                                $district = trim($territories[$key]);
                            }
                        }

                        // если не удалось обнаружить принадлежность (распарсить)
                        if (!$region && !$district) {
                            $this->parseFail($city->name . ': ' . $terminal->fullAddress);
                        }

                        TerminalDellin::create([
                            'identifier' => $city->id,
                            'name' => $city->name,
                            'district' => $district
                                ? $this->cleanDistrictName($district)
                                : null,
                            'region' => $region
                                ? $this->cleanRegionName($region)
                                : null,
                            'federal' => $federal,
                            'country' => 'RU',
                        ]);

                        $iterable++;
                    }
                }
            }
        }

        $timeEnd = Carbon::now();
        $executionTime = $timeStart->diffInSeconds($timeEnd);

        dump("Добавлено $iterable новых терминалов. $executionTime сек.");
    }

    /**
     * Записывает в лог-файл данные, которые не удалось распарсить.
     * 
     * @param $unknown
     * @return void
     */
    private function parseFail($unknown): void
    {
        Log::channel('parse')->warning('Dellin', [$unknown]);
    }

    /**
     * Возвращает чистое название района.
     */
    private function cleanDistrictName(string $name): string
    {
        // замена нежелательных вхождений
        $name = str_replace(['р-н'], ['район'], $name);

        // замена всей строки
        $badDistrictName = [
            1 => "Коммунистов (Зарека район) ул",
            2 => "Краснодонская (Центральный район) ул.",
            3 => "А.А.Айдамирова (Ахматовский район) ул",
        ];

        $goodDistrictName = [
            1 => "Зарека район",
            2 => "Центральный район",
            3 => "Ахматовский район",
        ];

        $key = array_search($name, $badDistrictName);
        if ($key >= 1) {
            return $goodDistrictName[$key];
        }

        return $name;
    }

    /**
     * Возвращает чистое название региона.
     */
    private function cleanRegionName(string $name): string
    {
        $name = str_replace(['Республика', 'Автономный округ', 'область'], ['Респ', 'АО', 'обл'], $name);
        $name = str_replace(['Респ', 'АО', 'обл'], ['Республика', 'автономный округ', 'область'], $name);

        // Кемеровская область - Кузбасс область
        // Ханты-Мансийский автономный округ - Югра автономный округ

        return str_replace([
            0 => 'Башкортостан Республика',
            1 => 'Татарстан Республика',
            2 => 'Карелия Республика',
            3 => 'Коми Республика',
            4 => 'Марий Эл Республика',
            5 => 'Бурятия Республика',
            6 => 'Хакасия Республика',
            7 => 'Мордовия Республика',
            8 => 'Крым Республика',
            9 => 'Северная Осетия - Алания Республика',
            10 => 'Дагестан Республика',
            11 => 'Адыгея Республика',
            12 => 'Саха (Якутия) Республика',
            13 => 'Алтай Республика',
            14 => 'Тыва Республика',
            15 => 'Кемеровская область - Кузбасс область',
            16 => 'Ханты-Мансийский автономный округ - Югра автономный округ',
        ], [
            0 => 'Республика Башкортостан',
            1 => 'Республика Татарстан',
            2 => 'Республика Карелия',
            3 => 'Республика Коми',
            4 => 'Республика Марий Эл',
            5 => 'Республика Бурятия',
            6 => 'Республика Хакасия',
            7 => 'Республика Мордовия',
            8 => 'Республика Крым',
            9 => 'Республика Северная Осетия-Алания',
            10 => 'Республика Дагестан',
            11 => 'Республика Адыгея',
            12 => 'Республика Саха (Якутия)',
            13 => 'Республика Алтай',
            14 => 'Республика Тыва',
            15 => 'Кемеровская область',
            16 => 'Ханты-Мансийский автономный округ - Югра',
        ], $name);
    }
}
