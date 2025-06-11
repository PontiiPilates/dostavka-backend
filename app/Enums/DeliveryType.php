<?php

namespace App\Enums;

enum DeliveryType: string
{
    case Ss = 'ss';
    case Sd = 'sd';
    case Ds = 'ds';
    case Dd = 'dd';
    case Tt = 'tt';
    case Dp = 'dp';
    case Sp = 'sp';
    case Pd = 'pd';
    case Ps = 'ps';
    case Pp = 'pp';

    public function label(): string
    {
        return match ($this) {
            self::Ss => 'Склад-склад',
            self::Sd => 'Склад-дверь',
            self::Ds => 'Дверь-склад',
            self::Dd => 'Дверь-дверь',
            self::Tt => 'Терминал-терминал',
            self::Dp => 'Дверь-постомат',
            self::Sp => 'Склад-постомат',
            self::Pd => 'Постомат-дверь',
            self::Ps => 'Постомат-склад',
            self::Pp => 'Постомат-постомат',
        };
    }
}
