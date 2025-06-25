<?php

declare(strict_types=1);

namespace App\Enums\Baikal;

enum BaikalUrlType: string
{
    case Affiliate = '/v2/affiliate';
    case Calculator = '/v2/calculator';

    public function label()
    {
        return match ($this) {
            self::Affiliate => 'Получение списка всех филиалов',
            self::Calculator => 'Расчёт стоимости доставки',
        };
    }
}
