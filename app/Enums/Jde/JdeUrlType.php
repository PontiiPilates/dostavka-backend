<?php

declare(strict_types=1);

namespace App\Enums\Jde;

enum JdeUrlType: string
{
    case Calculator = '/calculator/price';
    case Geo = '/geo/search';
    case Type = '/calculator/PriceTypeListAvailable';

    public function label()
    {
        return match ($this) {
            self::Calculator => 'Калькулятор',
            self::Geo => 'Терминалы',
            self::Type => 'Способы доставки',
        };
    }
}
