<?php

declare(strict_types=1);

namespace App\interfaces;

use Illuminate\Http\Request;

interface CaseInterface
{
    public function handle(Request $request);
}
