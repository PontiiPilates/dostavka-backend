<?php

declare(strict_types=1);

namespace App\Builders\Boxberry;

class DeliveryTypeBuilder
{
    public function sS(string $fromTerminal, string $toTerminal): array
    {
        return [
            "Departure" => [
                "CityGuid" => $fromTerminal // идентификатор из справочника населенных пунктов
            ],
            "Destination" => [
                "CityGuid" => $toTerminal // идентификатор из справочника населенных пунктов
            ],
        ];
    }

    public function sD(string $fromTerminal, string $toTerminal): array
    {
        return [
            "Departure" => [
                "CityGuid" => $fromTerminal // идентификатор из справочника населенных пунктов
            ],
            "Destination" => [
                "CityGuid" => $toTerminal, // идентификатор из справочника населенных пунктов
                'Delivery' => $this->emptyAddress()
            ],
        ];
    }
    public function dS(string $fromTerminal, string $toTerminal): array
    {
        return [
            "Departure" => [
                "CityGuid" => $fromTerminal, // идентификатор из справочника населенных пунктов
                'PickUp' => $this->emptyAddress()
            ],
            "Destination" => [
                "CityGuid" => $toTerminal // идентификатор из справочника населенных пунктов
            ],
        ];
    }
    public function dD(string $fromTerminal, string $toTerminal): array
    {
        return [
            "Departure" => [
                "CityGuid" => $fromTerminal, // идентификатор из справочника населенных пунктов
                'PickUp' => $this->emptyAddress()
            ],
            "Destination" => [
                "CityGuid" => $toTerminal, // идентификатор из справочника населенных пунктов
                'Delivery' => $this->emptyAddress()
            ],
        ];
    }

    private function emptyAddress()
    {
        return [
            'Street' => '',
            'House' => '',
            'TimeFrom' => '',
            'TimeTo' => '',
            'Services' => []
        ];
    }
}
