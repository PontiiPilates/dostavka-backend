<?php

declare(strict_types=1);

namespace App\Interfaces;

use Illuminate\Http\Client\Pool;

interface QueryPoolBuilderInterface
{
    public function build(array $request, Pool $pool): array;
}
