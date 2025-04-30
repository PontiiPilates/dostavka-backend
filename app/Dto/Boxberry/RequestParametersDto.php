<?php

declare(strict_types=1);

namespace App\Dto\Boxberry;

use App\interfaces\DtoInterface;

class RequestParametersDto implements DtoInterface
{

    public function __construct(
        private readonly string $token,
        private readonly string $method,
        private readonly string $senderCityId,
        private readonly string $recipientCityId,
        private readonly int $deliveryType,
        private readonly string $orderSum,
        private readonly array $boxSizes,
    ) {}

    public function toArray(): array
    {
        return [
            'token' => $this->token,
            'method' => $this->method,
            "SenderCityId" => $this->senderCityId,
            "RecipientCityId" => $this->recipientCityId,
            "DeliveryType" => $this->deliveryType,
            "OrderSum" => $this->orderSum,
            "BoxSizes" => $this->boxSizes,
            "Version" => "2.2"
        ];
    }
}
