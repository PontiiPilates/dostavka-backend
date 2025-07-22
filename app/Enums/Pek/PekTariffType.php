<?php

declare(strict_types=1);

namespace App\Enums\Pek;

enum PekTariffType: int
{
    case AviaExpress = 1;
    case Auto = 3;
    case AutoExpress = 5;
    case AutoDts = 7;
    case AutoEasyWay = 12;

    public function label()
    {
        return match ($this) {
            self::AviaExpress => 'Express Авиаперевозка',
            self::Auto => 'LTL Автоперевозка (сборный груз)',
            self::AutoExpress => 'Express Автоперевозка (сборный груз ускоренный)',
            self::AutoDts => 'Доставка в торговые сети Автоперевозка (паллеты)',
            self::AutoEasyWay => 'EasyWay Автоперевозка (Доставка для интернет магазинов)',
        };
    }
}
