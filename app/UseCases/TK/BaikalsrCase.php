<?php

declare(strict_types=1);

namespace App\UseCases\TK;

use Illuminate\Support\Facades\Http;

class BaikalsrCase extends BaseCase
{
    public function handle()
    {

        return [
            'tariff_name' => 'someBaikalName',
            'tariff_number' => 'someBaikalNumber',
            'pay' => '9999',
            'errors' => [],
            'deadline' => 'someDeadline',
            'days' => 'someCountDays',
        ];
    }
}
