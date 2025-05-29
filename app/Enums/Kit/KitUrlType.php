<?php

declare(strict_types=1);

namespace App\Enums\Kit;

enum KitUrlType: string
{
    case City = '/1.1/tdd/city/get-list';
    case Calculate = '/1.1/order/calculate';
    // case Type = '/calculator/PriceTypeListAvailable';

    public function label()
    {
        return match ($this) {
            self::City => 'Список городов',
            self::Calculate => 'Расчёт стоимости доставки',
            // self::Type => 'Способы доставки',
        };
    }
}
