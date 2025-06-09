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
    case Jde = 'jde';
    case Kit = 'kit';
    case Pek = 'pek';
    case Cdek = 'cdek';

    public function label()
    {
        return match ($this) {
            self::Pochta => 'Почта России',
            self::Baikal => 'Байкал Сервис',
            self::DPD => 'DPD',
            self::Boxberry => 'Boxberry',
            self::Vozovoz => 'Boxberry',
            self::Dellin => 'Деловые линии',
            self::Kit => 'Кит',
            self::Pek => 'ПЭК',
            self::Cdek => 'СДЕК',
        };
    }
}
