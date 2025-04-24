<?php

declare(strict_types=1);

namespace App\Enums\DPD;

enum DpdUrlType: string
{
    case Geography = 'geography2?wsdl';
    case Calculator = 'calculator2?wsdl';
}
