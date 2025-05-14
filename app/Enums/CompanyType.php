<?php

declare(strict_types=1);

namespace App\Enums;

enum  CompanyType: string
{
    case Pochta = 'pochta';
    case Baikal = 'baikal';
    case DPD = 'dpd';
    case Boxberry = 'boxberry';
    case Vozovoz = 'vozovoz';
    case Dellin = 'dellin';

    public function label()
    {
        return match ($this) {
            self::Pochta => 'Почта России',
            self::Baikal => 'Байкал Сервис',
            self::DPD => 'DPD',
            self::Boxberry => 'Boxberry',
            self::Vozovoz => 'Boxberry',
            self::Dellin => 'Деловые линии',
        };
    }
}
