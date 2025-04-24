<?php

declare(strict_types=1);

namespace App\UseCases\TK;

use Illuminate\Support\Facades\DB;

class BaseCase
{
    /**
     * Проверка: является ли доставка интернациональной.
     */
    public function isInternational($code): bool
    {
        if ($code == 643) return false;
        return DB::table('countries')->where('code', $code)->exists();
    }
}
