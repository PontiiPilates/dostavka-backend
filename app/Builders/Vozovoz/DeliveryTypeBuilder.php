<?php

declare(strict_types=1);

namespace App\Builders\Vozovoz;

use App\Models\Tk\TerminalVozovoz;

class DeliveryTypeBuilder
{
    public function sS(TerminalVozovoz $from, TerminalVozovoz $to, string $date): array
    {
        return [
            "dispatch" => [
                "point" => [
                    "location" => $from->identifier,
                    "terminal" => "default",
                    "date" => $date
                ]
            ],
            "destination" => [
                "point" => [
                    "location" => $to->identifier,
                    "terminal" => "default",
                    "date" => $date
                ]
            ]
        ];
    }
    public function sD(TerminalVozovoz $from, TerminalVozovoz $to, string $date): array
    {
        return [
            "dispatch" => [
                "point" => [
                    "location" => $from->identifier,
                    "terminal" => "default",
                    "date" => $date
                ]
            ],
            "destination" => [
                "point" => [
                    "location" => $to->identifier,
                    "address" => "",
                    "date" => $date
                ]
            ]
        ];
    }
    public function dS(TerminalVozovoz $from, TerminalVozovoz $to, string $date): array
    {
        return [
            "dispatch" => [
                "point" => [
                    "location" => $from->identifier,
                    "address" => "",
                    "date" => $date
                ]
            ],
            "destination" => [
                "point" => [
                    "location" => $to->identifier,
                    "terminal" => "default",
                    "date" => $date
                ]
            ]
        ];
    }
    public function dD(TerminalVozovoz $from, TerminalVozovoz $to, string $date): array
    {
        return [
            "dispatch" => [
                "point" => [
                    "location" => $from->identifier,
                    "address" => "",
                    "date" => $date
                ]
            ],
            "destination" => [
                "point" => [
                    "location" => $to->identifier,
                    "address" => "",
                    "date" => $date
                ]
            ]
        ];
    }
}
