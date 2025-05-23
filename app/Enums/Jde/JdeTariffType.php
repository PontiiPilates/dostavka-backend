<?php

declare(strict_types=1);

namespace App\Enums\Jde;

enum JdeTariffType: int
{
    case Combined = 1;
    case Express = 2;
    case Individual = 3;
    case Internet = 6;
    case Courier = 7;

    public function label()
    {
        return match ($this) {
            self::Combined => 'Доставка сборных грузов',
            self::Express => 'Экспресс доставка грузов',
            self::Individual => 'Индивидуальная доставка грузов',
            self::Internet => 'Интернет-посылка',
            self::Courier => 'Курьерская доставка',
        };
    }
}
