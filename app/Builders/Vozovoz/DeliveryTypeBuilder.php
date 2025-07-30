<?php

declare(strict_types=1);

namespace App\Builders\Vozovoz;

class DeliveryTypeBuilder
{
    public function sS(string $from, string $to, string $date): array
    {
        return [
            "dispatch" => [
                "point" => [
                    "location" => $from,
                    "terminal" => "default",
                    "date" => $date
                ]
            ],
            "destination" => [
                "point" => [
                    "location" => $to,
                    "terminal" => "default",
                    "date" => $date
                ]
            ]
        ];
    }
    public function sD(string $from, string $to, string $date): array
    {
        return [
            "dispatch" => [
                "point" => [
                    "location" => $from,
                    "terminal" => "default",
                    "date" => $date
                ]
            ],
            "destination" => [
                "point" => [
                    "location" => $to,
                    "address" => "",
                    "date" => $date
                ]
            ]
        ];
    }
    public function dS(string $from, string $to, string $date): array
    {
        return [
            "dispatch" => [
                "point" => [
                    "location" => $from,
                    "address" => "",
                    "date" => $date
                ]
            ],
            "destination" => [
                "point" => [
                    "location" => $to,
                    "terminal" => "default",
                    "date" => $date
                ]
            ]
        ];
    }
    public function dD(string $from, string $to, string $date): array
    {
        return [
            "dispatch" => [
                "point" => [
                    "location" => $from,
                    "address" => "",
                    "date" => $date
                ]
            ],
            "destination" => [
                "point" => [
                    "location" => $to,
                    "address" => "",
                    "date" => $date
                ]
            ]
        ];
    }
}
