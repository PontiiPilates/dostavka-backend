<?php

declare(strict_types=1);

namespace App\Builders\Dellin;

use App\Models\Tk\TerminalDellin;

class DeliveryTypeBuilder
{
    public function sS(TerminalDellin $from, TerminalDellin $to, string $date, string $tariff): array
    {
        return [
            "deliveryType" => [
                "type" => $tariff
            ],
            "derival" => [
                "produceDate" => $date,
                "variant" => "terminal",
                "terminalID" => $from->terminal_id
            ],
            "arrival" => [
                "variant" => "terminal",
                "terminalID" => $to->terminal_id
            ],
        ];
    }
    public function sD(TerminalDellin $from, TerminalDellin $to, string $date, string $tariff): array
    {
        return [
            "deliveryType" => [
                "type" => $tariff
            ],
            "derival" => [
                "produceDate" => $date,
                "variant" => "terminal",
                "terminalID" => $from->terminal_id
            ],
            "arrival" => [
                "variant" => "address",
                "address" => [
                    "search" => $to->city_id
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
    public function dS(TerminalDellin $from, TerminalDellin $to, string $date, string $tariff): array
    {
        return [
            "deliveryType" => [
                "type" => $tariff
            ],
            "derival" => [
                "produceDate" => $date,
                "variant" => "address",
                "address" => [
                    "search" => $from->city_id
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
                "terminalID" => $to->terminal_id
            ],
        ];
    }
    public function dD(TerminalDellin $from, TerminalDellin $to, string $date, string $tariff): array
    {
        return [
            "deliveryType" => [
                "type" => $tariff
            ],
            "derival" => [
                "produceDate" => $date,
                "variant" => "address",
                "address" => [
                    "search" => $from->city_id
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
                    "search" => $to->city_id
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
