<?php

declare(strict_types=1);

namespace App\Enums\Dellin;

enum DellinTariffType: string
{
    case Small = 'small';
    case Auto = 'auto';
    case Express = 'express';
    case Avia = 'avia';

    public function label()
    {
        return match ($this) {
            self::Small => 'Перевозка малогабаритного груза',
            self::Auto => 'Стандартная перевозка',
            self::Express => 'Экспресс-доставка',
            self::Avia => 'Перевозка воздушным транспортом',
        };
    }
}
