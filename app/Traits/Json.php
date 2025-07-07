<?php

namespace App\Traits;

trait Json
{
    public function toJson(array|string|object $data): string
    {
        return json_encode($data);
    }

    public function toArray(string $data): array
    {
        return json_decode($data, true);
    }

    public function toObject(string $data): object
    {
        return json_decode($data);
    }
}
