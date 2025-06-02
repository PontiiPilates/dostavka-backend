<?php

declare(strict_types=1);

namespace App\Enums\Pek;

enum PekUrlType: string
{
    case Terminals = '/branches/all';
    case Calculate = '/calculator/calculateprice';
    case Tariffs = '/typesOfDelivery/all';

    public function label()
    {
        return match ($this) {
            self::Terminals => 'Список городов и терминалов',
            self::Calculate => 'Расчёт стоимости и сроков доставки',
            self::Tariffs => 'Способы доставки',
        };
    }
}
