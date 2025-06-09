<?php

declare(strict_types=1);

namespace App\Enums\Cdek;

enum CdekUrlType: string
{
    case Auth = '/v2/oauth/token';
    case Cities = '/v2/location/cities';

    public function label()
    {
        return match ($this) {
            self::Auth => 'Авторизация в сервисе',
            self::Cities => 'Получение списка населённых пунктов',
        };
    }
}
