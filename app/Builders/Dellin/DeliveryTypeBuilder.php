<?php

declare(strict_types=1);

namespace App\Builders\Dellin;

use App\Models\City;

class DeliveryTypeBuilder
{
    public function sS(City $from, City $to, string $date, string $tariff): array
    {
        return [
            "deliveryType" => [
                "type" => $tariff
            ],
            "derival" => [
                "produceDate" => $date,
                "variant" => "terminal",
                "terminalID" => $from->terminal_id_dellin
            ],
            "arrival" => [
                "variant" => "terminal",
                "terminalID" => $to->terminal_id_dellin
            ],
        ];
    }
    public function sD(City $from, City $to, string $date, string $tariff): array
    {
        return [
            "deliveryType" => [
                "type" => $tariff
            ],
            "derival" => [
                "produceDate" => $date,
                "variant" => "terminal",
                "terminalID" => $from->terminal_id_dellin
            ],
            "arrival" => [
                "variant" => "address",
                "address" => [
                    "search" => $to->city_code_dellin
                ],
                "time" => [
                    "worktimeEnd" => "19:30",
                    "worktimeStart" => "9:00",
                    "breakStart" => "12:00",
                    "breakEnd" => "13:00",
                    "exactTime" => false
                ]
            ],
        ];
    }
    public function dS(City $from, City $to, string $date, string $tariff): array
    {
        return [
            "deliveryType" => [
                "type" => $tariff
            ],
            "derival" => [
                "produceDate" => $date,
                "variant" => "address",
                "address" => [
                    "search" => $from->city_code_dellin
                ],
                "time" => [
                    "worktimeEnd" => "19:30",
                    "worktimeStart" => "9:00",
                    "breakStart" => "12:00",
                    "breakEnd" => "13:00",
                    "exactTime" => false
                ]
            ],
            "arrival" => [
                "variant" => "terminal",
                "terminalID" => $to->terminal_id_dellin
            ],
        ];
    }
    public function dD(City $from, City $to, string $date, string $tariff): array
    {
        return [
            "deliveryType" => [
                "type" => $tariff
            ],
            "derival" => [
                "produceDate" => $date,
                "variant" => "address",
                "address" => [
                    "search" => $from->city_code_dellin
                ],
                "time" => [
                    "worktimeEnd" => "19:30",
                    "worktimeStart" => "9:00",
                    "breakStart" => "12:00",
                    "breakEnd" => "13:00",
                    "exactTime" => false
                ]
            ],
            "arrival" => [
                "variant" => "address",
                "address" => [
                    "search" => $to->city_code_dellin
                ],
                "time" => [
                    "worktimeEnd" => "19:30",
                    "worktimeStart" => "9:00",
                    "breakStart" => "12:00",
                    "breakEnd" => "13:00",
                    "exactTime" => false
                ]
            ],
        ];
    }
}
