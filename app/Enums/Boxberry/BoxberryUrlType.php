<?php

declare(strict_types=1);

namespace App\Enums\Boxberry;

enum BoxberryUrlType: string
{
    case DeliveryCalculation = 'DeliveryCalculation';
    case ListCities = 'ListCities';
}
