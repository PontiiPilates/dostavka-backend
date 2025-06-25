<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\City;
use App\Models\Country;
use App\Models\Location;
use App\Models\Region;
use App\Models\Tk\TerminalJde;
use App\Models\Tk\TerminalNrg;
use App\Models\TkPekTerminal;
use App\Traits\Tk\Cdek\SuggestTerminal;
use Exception;
use Illuminate\Database\Eloquent\Builder;

/**
 * Преобразует пользовательский ввод локации в данные, которые требуются для взаимодействия с транспортной компанией.
 * 
 * @property string $cityName
 * @property string $countryName;
 * 
 * @method TerminalJde specialFromJde($location $direction)
 */
class LocationService
{
    use SuggestTerminal;

    private string $cityName;
    private string $countryName;
    private string $countryAlpha2;

    /**
     * Возвращает сущность города на основе строки в формате "Населённый пункт, Страна".
     * 
     * @param string $location 
     * @return City
     */
    public function city(string $location): Location
    {
        $this->parseLocation($location);

        $city = $this->findCity();

        if (!$city) {
            throw new Exception("Населённого пункта не существует", 404);
        }

        return $city;
    }

    /**
     * Терминалы данной компании снабжены атрибумами приёма/выдачи груза.
     * В связи с этим не лишним будет убедиться в том, что целевой терминал способен принимать/выдавать груз.
     * 
     * @param string $location
     * @param string $direction
     * @return TerminalJde
     */
    public function specialFromJde(string $location, string $direction): TerminalJde
    {
        $city = $this->city($location);

        try {
            switch ($direction) {
                case 'from':
                    return $city->terminalsJde()->where(['acceptance' => true])->firstOrFail();
                    break;
                case 'to':
                    return $city->terminalsJde()->where(['issue' => true])->firstOrFail();
                    break;
            }
        } catch (\Throwable $th) {
            throw new Exception("Компания будет исключена из расчётов. Терминал населённого пункта не может принимать/выдавать груз", 500);
        }
    }

    public function tkPek(string $location, array $cargo): TkPekTerminal
    {
        $city = $this->city($location);

        // транспортная компания использует нули в качестве обозначения терминалов без ограничений
        // при обращении к населённому пункту запрос ориентирован на терминалы без ограничений
        // если терминала без ограничений не существует в населенном пункте
        // то запрашиваются терминалы, способные принять груз с указанными параметрами
        // иначе не следует продолжать выполнение
        try {
            return $city->tkPek()
                ->where(function ($query) use ($cargo) {
                    $query->where([
                        ['max_weight', '=', 0],
                        ['max_volume', '=', 0],
                        ['max_weight_per_place', '=', 0],
                        ['max_dimension', '=', 0],
                    ])->orWhere(function (Builder $query) use ($cargo) {
                        $query->where([
                            ['max_weight', '>=', $cargo['maxWeight']],
                            ['max_volume', '>=', $cargo['maxVolume']],
                            ['max_weight_per_place', '>=', $cargo['maxWeightPerPlace']],
                            ['max_dimension', '>=', $cargo['maxDimension']],
                        ]);
                    });
                })
                ->firstOrFail();
        } catch (\Throwable $th) {
            throw new Exception("Не обнаружен терминал, способный принять груз. {$th->getMessage()}", 500);
        }
    }

    /**
     * Возвращает идентификатор терминала.
     * 
     * Для СДЕК требуются идентификаторы терминалов.
     * В случае с перевозкой по России, идентификаторы хранятся в базе данных.
     * В случае с международной перевозкой, идентификаторы нужно получить в результате запроса.
     */
    public function fromCdek(string $location): int
    {
        $this->parseLocation($location);

        $terminal = Country::where('name', $this->countryName)->first()->cities()->where('city_name', $this->cityName)->first()->terminalsCdek()->first();

        if ($terminal) {
            $terminalCode = $terminal->terminal_id;
        } else {
            $terminalCode = self::terminalIdByLocation($this->cityName, $this->countryName, $this->countryAlpha2);
        }

        return (int) $terminalCode;
    }

    private function parseLocation(string $location): void
    {
        $location = str_replace(' ', '', $location);
        $items = explode(',', $location);

        $this->cityName = $items[0];
        $this->countryName = $items[1];

        try {
            $this->countryAlpha2 = Country::where('name', $items[1])->firstOrFail()->alpha2;
        } catch (\Throwable $th) {
            throw new Exception("Запрашиваемой страны не существует. {$th->getMessage()}", 404);
        }
    }

    private function findCity(): Location
    {
        return Location::query()
            ->where('city_name', $this->cityName)
            ->whereHas('country', function (Builder $query) {
                $query->where('name', $this->countryName);
            })->firstOrFail();
    }

    /**
     * В данный момент этот метод представляет собой рабочую заглушку.
     * Окончательный вариант появится после завершения работы с базой данных.
     */
    public function fromNrg($location)
    {
        $items = explode(',', $location);

        $primaryName = trim($items[0]);
        $secondaryName = trim($items[1]);

        // return TerminalNrg::where([
        //     ['name', '=', $primaryName],
        //     ['description', '=', $secondaryName],
        // ])->firstOrFail();

        return TerminalNrg::whereAny(['name', 'description'], 'like', "$primaryName%")->firstOrFail();

        // $searchTerms = [];
        // foreach ($items as $item) {
        //     $searchTerms[] = trim($item);
        // }

        // $results = TerminalNrg::where(function ($q) use ($searchTerms) {
        //     foreach ($searchTerms as $term) {
        //         $q->orWhere('name', 'LIKE', "%{$term}%")
        //             ->orWhere('description', 'LIKE', "%{$term}%");
        //     }
        // })->dd();


    }

    public function newParser()
    {
        // Если город федерального значения, то принадлежность к региону не нужна
        // Соответственно должен быть список городов федерального значения

        // Возможно список регионов не нужен и для крупных городов, чьи имена уникальные
        // К таким городам можно присоединять указание страны

        // Но ко всем остальным нужно применять регион
    }

    public function location($location)
    {
        $items = explode(',', $location);

        $place = trim($items[0]);
        $region = trim($items[1]);

        return Region::where('name', $region)->first()->locations()->where('name', $place)->first();
    }
}
