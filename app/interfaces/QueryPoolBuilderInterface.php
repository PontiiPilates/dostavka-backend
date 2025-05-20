<?php

declare(strict_types=1);

namespace App\Interfaces;

use Illuminate\Http\Client\Pool;
use Illuminate\Http\Request;

interface QueryPoolBuilderInterface
{
    public function build(Request $request, Pool $pool): array|null;
}
