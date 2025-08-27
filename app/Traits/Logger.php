<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;

trait Logger
{
    /**
     * Записывает в соответствующий канал элементы, которые не удалось распарсить.
     * 
     * @param string $company
     * @param string $message
     * @return void
     */
    public function parseFail(string $company, string $message): void
    {
        Log::channel('parse')->warning($company . ": $message");
    }
}
