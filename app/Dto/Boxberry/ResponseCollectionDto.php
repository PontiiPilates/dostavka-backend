<?php

declare(strict_types=1);

namespace App\Dto\Boxberry;

use App\Interfaces\DtoInterface;

class ResponseCollectionDto implements DtoInterface
{
    private array $collection;

    public function toArray(): array
    {
        return $this->collection;
    }

    public function setItem(string $mode, array $item)
    {
        $this->collection[$mode][] = $item;
    }
}
