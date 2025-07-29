<?php

declare(strict_types=1);

namespace App\Enums\Pochta;

enum PochtaUrlType: string
{
    case Calculate = '/v2/calculate/tariff/delivery';
    case DictionaryObject = '/v2/dictionary/object/tariff/delivery';

    public function label()
    {
        return match ($this) {
            self::Calculate => 'Расчёт стоимости и сроков доставки',
            self::DictionaryObject => 'Описание объекта расчёта',
        };
    }
}
