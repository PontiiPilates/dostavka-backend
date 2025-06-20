<?php

declare(strict_types=1);

namespace App\Enums\Kit;

enum KitUrlType: string
{
    case City = '/1.1/tdd/city/get-list';
    case Calculate = '/1.1/order/calculate';
    case Regions = '/1.0/tdd/region/get-list';
    // case Type = '/calculator/PriceTypeListAvailable';

    public function label()
    {
        return match ($this) {
            self::City => 'Список городов',
            self::Calculate => 'Расчёт стоимости доставки',
            self::Regions => 'Список регионов',
            // self::Type => 'Способы доставки',
        };
    }
}
