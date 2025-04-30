<?php

declare(strict_types=1);

namespace App\Dto\Boxberry;

use App\interfaces\DtoInterface;

class OfferDto implements DtoInterface
{
    public function __construct(
        private readonly string|null $tariff,
        private readonly float $cost,
        private readonly int $days,
    ) {}

    public function toArray(): array
    {
        return [
            "tariff" => $this->tariff,
            "cost" => $this->cost,
            "days" => $this->days
        ];
    }
}
