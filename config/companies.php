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
];
