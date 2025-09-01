<?php

declare(strict_types=1);

namespace App\Builders\Baikal;

use App\Models\Tk\TerminalBaikal;

class DeliveryTypeBuilder
{
    public function sS(TerminalBaikal $from, TerminalBaikal $to): array
    {
        return [
            "Departure" => [
                "CityGuid" => $from->identifier,
            ],
            "Destination" => [
                "CityGuid" => $to->identifier,
            ],
        ];
    }

    public function sD(TerminalBaikal $from, TerminalBaikal $to): array
    {
        return [
            "Departure" => [
                "CityGuid" => $from->identifier,
            ],
            "Destination" => [
                "CityGuid" => $to->identifier,
                'Delivery' => $this->emptyAddress()
            ],
        ];
    }
    public function dS(TerminalBaikal $from, TerminalBaikal $to): array
    {
        return [
            "Departure" => [
                "CityGuid" => $from->identifier,
                'PickUp' => $this->emptyAddress()
            ],
            "Destination" => [
                "CityGuid" => $to->identifier
            ],
        ];
    }
    public function dD(TerminalBaikal $from, TerminalBaikal $to): array
    {
        return [
            "Departure" => [
                "CityGuid" => $from->identifier,
                'PickUp' => $this->emptyAddress()
            ],
            "Destination" => [
                "CityGuid" => $to->identifier,
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
