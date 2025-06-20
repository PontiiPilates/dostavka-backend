<?php

use App\Enums\CompanyType;

return [
    CompanyType::Pochta->value => [
        'url' => env('POCHTA_URL'),
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
    CompanyType::Dellin->value => [
        'url' => env('DELLIN_URL'),
        'token' => env('DELLIN_TOKEN'),
    ],
    CompanyType::Jde->value => [
        'url' => env('JDE_URL'),
        'token' => env('JDE_TOKEN'),
        'user' => env('JDE_USER'),
    ],
    CompanyType::Kit->value => [
        'url' => env('KIT_URL'),
        'token' => env('KIT_TOKEN'),
    ],
    CompanyType::Pek->value => [
        'url' => env('PEK_URL'),
        'user' => env('PEK_USER'),
        'password' => env('PEK_PASSWORD'),
    ],
    CompanyType::Cdek->value => [
        'url' => env('CDEK_URL'),
        'account' => env('CDEK_ACCOUNT'),
        'secure' => env('CDEK_SECURE_PASSWORD'),
    ],
    CompanyType::Nrg->value => [
        'url' => env('NRG_URL'),
        'token' => env('NRG_TOKEN'),
    ],
    CompanyType::Baikal->value => [
        'url' => env('BAIKAL_URL'),
        'username' => env('BAIKAL_USERNAME'),
        'password' => env('BAIKAL_PASSWORD'),
    ],
];
