<?php

namespace App\Services\Clients\Tk;

use App\Interfaces\ClientInterface;
use stdClass;

class PostSoap implements ClientInterface
{

    public function send(string $url, array $parameters): stdClass
    {
        return response()->object();
    }
}
