<?php

declare(strict_types=1);

namespace App\Enums\Pochta;

enum  PochtaPackagesType: string
{
    case S = '10';
    case M = '20';
    case L = '30';
    case XL = '40';
    case Unstandart = '99';

    public function label()
    {
        return match ($this) {
            self::S => 'Коробка S  26x17x8  cм',
            self::M => 'Коробка M  30x20x15 cм',
            self::L => 'Коробка L  40x27x18 см',
            self::XL => 'Коробка XL 53x36x22 см',
            self::Unstandart => 'Нестандартная упаковка',
        };
    }
}
