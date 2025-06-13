<?php

declare(strict_types=1);

namespace App\Enums\Nrg;

enum NrgUrlType: string
{
    case Cities = '/cities';

    public function label()
    {
        return match ($this) {
            self::Cities => 'Список городов',
        };
    }
}
