<?php

declare(strict_types=1);

namespace App\Enums\Dellin;

enum DellinUrlType: string
{
    case Calculator = '/v2/calculator.json';
    case Terminals = '/v3/public/terminals.json';

    public function label()
    {
        return match ($this) {
            self::Calculator => 'Калькулятор',
            self::Terminals => 'Терминалы',
        };
    }
}
