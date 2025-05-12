<?php

namespace App\Enums;

enum DeliveryType: string
{
    case Ss = 'ss';
    case Sd = 'sd';
    case Ds = 'ds';
    case Dd = 'dd';

    public function label(): string
    {
        return match ($this) {
            self::Ss => 'Склад-склад',
            self::Sd => 'Склад-дверь',
            self::Ds => 'Дверь-склад',
            self::Dd => 'Дверь-дверь',
        };
    }
}
