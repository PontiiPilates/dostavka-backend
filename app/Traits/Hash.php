<?php

namespace App\Traits;

trait Hash
{
    public function arrayToHash(array $data)
    {
        return md5(serialize($data));
    }
}
