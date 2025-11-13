<?php

namespace Database\Seeders\Tk;

use App\Enums\CompanyType;
use App\Enums\LocationType;
use App\Models\Tk\TerminalDellin;
use App\Traits\Logger;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class TerminalDellinSeeder extends Seeder
{
    use Logger;

    /**
     * Особенности:
     * апи тк работает с идентификаторами терминалов и список подробен для каждого из них,
     * однако на данном этапе интересует лишь терминал, основной для населённого пункта,
     * небольшой, не самый подробный и не самый системный список,
     * его назначение - регистрация собственных идентификаторов,
     * возможно дополнение в незначительной степени,
     */
    public function run(): void
    {
        $pathToTerminals = 'assets/geo/tk/dellin/terminals.json';

        $file = Storage::json($pathToTerminals);

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

                            // если обнаружена региональная принадлежность
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
                            $this->parseFail(CompanyType::Dellin->value, $city->name . ': ' . $terminal->fullAddress);
                        }

                        TerminalDellin::create([
                            'identifier' => $terminal->id,
                            'code' => $city->code,
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
        $executionTime = number_format((float) $executionTime, 1, '.');

        $this->command->info("Добавлено $iterable терминалов, $executionTime сек.");
    }

    /**
     * Возвращает чистое название района.
     */
    private function cleanDistrictName(string $name): string
    {
        // замена нежелательных вхождений
        $name = str_replace(['р-н'], [LocationType::District->value], $name);

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
        $name = str_replace([
            'Республика',
            'Автономный округ',
            'область'
        ], [
            'Респ',
            'АО',
            'обл'
        ], $name);

        $name = str_replace([
            'Респ',
            'АО',
            'обл'
        ], [
            LocationType::Republic->value,
            LocationType::AutonomousRegion->value,
            LocationType::Area->value
        ], $name);

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
