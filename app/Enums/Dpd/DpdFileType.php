<?php

declare(strict_types=1);

namespace App\Enums\DPD;

enum DpdFileType: string
{
    case CitiesCashPay = 'assets\geo\tk\dpd\cities_cash_pay.json';
    case ParcelShops = 'assets\geo\tk\dpd\parcel_shops_request.json';
    case TerminalsSelfDelivery2 = 'assets\geo\tk\dpd\terminals_self_delivery_2.json';
}
