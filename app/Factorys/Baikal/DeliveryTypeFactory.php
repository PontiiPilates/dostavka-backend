<?php

namespace App\Factorys\Baikal;

use App\Builders\Baikal\DeliveryTypeBuilder;
use App\Enums\DeliveryType;
use App\Models\Tk\TerminalBaikal;

class DeliveryTypeFactory
{
    public static function make(string $type, TerminalBaikal $from, TerminalBaikal $to): array
    {
        $builder = new DeliveryTypeBuilder();

        return match ($type) {
            DeliveryType::Ss->value => $builder->sS($from, $to),
            DeliveryType::Sd->value => $builder->sD($from, $to),
            DeliveryType::Ds->value => $builder->dS($from, $to),
            DeliveryType::Dd->value => $builder->dD($from, $to),
        };
    }
}
