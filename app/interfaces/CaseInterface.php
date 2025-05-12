<?php

declare(strict_types=1);

namespace App\Interfaces;

use Illuminate\Http\Request;

interface CaseInterface
{
    public function handle(Request $request): array;
}
