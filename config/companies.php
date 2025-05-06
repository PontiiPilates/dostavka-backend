<?php

use App\Enums\CompanyType;

return [
    CompanyType::Pochta->value => [
        'url' => env('POCHTA_URL'),
    ],
    CompanyType::Baikal->value => [
        'uri' => null,
    ],
    CompanyType::DPD->value => [
        'uri' => env('DPD_URI_PROD'),
        'client_number' => env('DPD_CLIENT_NUMBER'),
        'client_key' => env('DPD_CLIENT_KEY'),
    ],
    CompanyType::Boxberry->value => [
        'url' => env('BOXBERRY_URL'),
        'token' => env('BOXBERRY_TOKEN'),
    ],
    CompanyType::Vozovoz->value => [
        'url' => env('VOZOVOZ_URL'),
        'token' => env('VOZOVOZ_TOKEN'),
    ],
];
