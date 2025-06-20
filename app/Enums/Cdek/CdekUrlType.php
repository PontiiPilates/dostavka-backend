<?php

declare(strict_types=1);

namespace App\Enums\Cdek;

enum CdekUrlType: string
{
    case Auth = '/v2/oauth/token';
    case Cities = '/v2/location/cities';
    case TariffList = '/v2/calculator/tarifflist';
    case SuggestCities = '/v2/location/suggest/cities';
    case Regions = '/v2/location/regions';

    public function label()
    {
        return match ($this) {
            self::Auth => 'Авторизация в сервисе',
            self::Cities => 'Получение списка населённых пунктов',
            self::TariffList => 'Расчёт по доступным тарифам',
            self::SuggestCities => 'Подбор локации по названию города',
            self::Regions => 'Получение списка регионов',
        };
    }
}
