<?php

namespace App\Enums\Cdek;

enum  CdekDeliveryType: int
{
    case Dd = 1;
    case Ds = 2;
    case Sd = 3;
    case Ss = 4;
    case Tt = 5;
    case Dp = 6;
    case Sp = 7;
    case Pd = 8;
    case Ps = 9;
    case Pp = 10;

    public function label()
    {
        match ($this) {
            self::Dd => 'Дверь-дверь',
            self::Ds => 'Дверь-склад',
            self::Sd => 'Склад-дверь',
            self::Ss => 'Склад-склад',
            self::Tt => 'Терминал-терминал',
            self::Dp => 'Дверь-постомат',
            self::Sp => 'Склад-постомат',
            self::Pd => 'Постомат-дверь',
            self::Ps => 'Постомат-склад',
            self::Pp => 'Постомат-постомат',
        };
    }
}
