<?php

namespace App\Factorys\Vozovoz;

use App\Builders\Vozovoz\DeliveryTypeBuilder;
use App\Enums\DeliveryType;
use App\Models\City;

class DeliveryTypeFactory
{
    public static function make(string $type, City $from, City $to, string $date): array
    {
        $builder = new DeliveryTypeBuilder();

        return match ($type) {
            DeliveryType::Ss->value => $builder->sS($from, $to, $date),
            DeliveryType::Sd->value => $builder->sD($from, $to, $date),
            DeliveryType::Ds->value => $builder->dS($from, $to, $date),
            DeliveryType::Dd->value => $builder->dD($from, $to, $date),
        };
    }
}
