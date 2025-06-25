<?php

namespace App\Factorys\Baikal;

use App\Builders\Baikal\DeliveryTypeBuilder;
use App\Enums\DeliveryType;

class DeliveryTypeFactory
{
    public static function make(string $type, string $fromTerminal, string $toTerminal): array
    {
        $builder = new DeliveryTypeBuilder();

        return match ($type) {
            DeliveryType::Ss->value => $builder->sS($fromTerminal, $toTerminal),
            DeliveryType::Sd->value => $builder->sD($fromTerminal, $toTerminal),
            DeliveryType::Ds->value => $builder->dS($fromTerminal, $toTerminal),
            DeliveryType::Dd->value => $builder->dD($fromTerminal, $toTerminal),
        };
    }
}
