<?php

namespace App\Factorys\Dellin;

use App\Builders\Dellin\DeliveryTypeBuilder;
use App\Enums\DeliveryType;
use App\Models\Tk\TerminalDellin;

class DeliveryTypeFactory
{
    public static function make(string $type, TerminalDellin $from, TerminalDellin $to, string $date, string $tariff): array
    {
        $builder = new DeliveryTypeBuilder();

        return match ($type) {
            DeliveryType::Ss->value => $builder->sS($from, $to, $date, $tariff),
            DeliveryType::Sd->value => $builder->sD($from, $to, $date, $tariff),
            DeliveryType::Ds->value => $builder->dS($from, $to, $date, $tariff),
            DeliveryType::Dd->value => $builder->dD($from, $to, $date, $tariff),
        };
    }
}
