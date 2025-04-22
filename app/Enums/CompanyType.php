<?php

declare(strict_types=1);

namespace App\Enums;

enum  CompanyType: string
{
    case Pochta = 'pochta';
    case Baikal = 'baikal';

    public function label()
    {
        return match ($this) {
            self::Pochta => 'Почта России',
            self::Baikal => 'Байкал Сервис',
        };
    }
}
