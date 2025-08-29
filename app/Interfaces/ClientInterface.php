<?php

declare(strict_types=1);

namespace App\Interfaces;

use stdClass;

interface ClientInterface
{
    public function send(string $url, array $parameters): stdClass;
}
