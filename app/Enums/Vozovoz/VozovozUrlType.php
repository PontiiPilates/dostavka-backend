<?php

declare(strict_types=1);

namespace App\Enums\Vozovoz;

enum VozovozUrlType: string
{
    case Price = 'price';
    case Schedule = 'schedule';
    case Terminal = 'terminal';
    case Wrapping = 'wrapping';
    case Location = 'location';

    public function label()
    {
        return match ($this) {
            self::Price => 'Объект - стоимость',
            self::Schedule => 'Объект - расписание доставки',
            self::Terminal => 'Объект - терминал',
            self::Wrapping => 'Объект - упаковка',
            self::Location => 'Объект - локации',
        };
    }
}
