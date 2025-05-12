<?php

declare(strict_types=1);

namespace App\Services\Location;

use App\Models\City;
use Exception;

class LocationParserService
{
    /**
     * Возвращает исчерпывающую информацию о населённом пункте.
     * 
     * @param string $location в формате "Населённый пункт, Страна".
     * @return City
     */
    public function moreAboutCity($location): City
    {
        $location = str_replace(' ', '', $location);
        $items = explode(',', $location);

        $city = City::where('city_name', $items[0])->where('country_name', $items[1])->first();

        if (!$city) {
            throw new Exception("Некорректное значение места отправления/получения", 404);
        }

        return $city;
    }
}
