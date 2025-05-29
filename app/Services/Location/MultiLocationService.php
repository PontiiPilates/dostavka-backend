<?php

declare(strict_types=1);

namespace App\Services\Location;

use App\Models\City;
use App\Models\Tk\TerminalJde;
use Exception;
use Illuminate\Database\Eloquent\Builder;

/**
 * Содержит методы, коорые возвращают специализированные обозначения пунктов отправки/получения груза для каждой интеграции.
 * 
 * @property string $cityName
 * @property string $countryName;
 * 
 * @method TerminalJde specialFromJde($location $direction)
 */
class MultiLocationService
{
    private string $cityName;
    private string $countryName;

    /**
     * Возвращает сущность города на основе строки в формате "Населённый пункт, Страна".
     * 
     * @param string $location 
     * @return City
     */
    public function city(string $location): City
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

    private function parseLocation(string $location): void
    {
        $location = str_replace(' ', '', $location);
        $items = explode(',', $location);

        $this->cityName = $items[0];
        $this->countryName = $items[1];
    }

    private function findCity(): City
    {
        return City::query()
            ->where('city_name', $this->cityName)
            ->whereHas('country', function (Builder $query) {
                $query->where('name', $this->countryName);
            })->firstOrFail();
    }
}
