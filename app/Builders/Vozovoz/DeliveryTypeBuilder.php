<?php

declare(strict_types=1);

namespace App\Builders\Vozovoz;

use App\Models\City;

class DeliveryTypeBuilder
{
    public function sS(City $from, City $to, string $date): array
    {
        return [
            "dispatch" => [
                "point" => [
                    "location" => $from->city_id_vozovoz,
                    "terminal" => "default",
                    "date" => $date
                ]
            ],
            "destination" => [
                "point" => [
                    "location" => $to->city_id_vozovoz,
                    "terminal" => "default",
                    "date" => $date
                ]
            ]
        ];
    }
    public function sD(City $from, City $to, string $date): array
    {
        return [
            "dispatch" => [
                "point" => [
                    "location" => $from->city_id_vozovoz,
                    "terminal" => "default",
                    "date" => $date
                ]
            ],
            "destination" => [
                "point" => [
                    "location" => $to->city_id_vozovoz,
                    "address" => "",
                    "date" => $date
                ]
            ]
        ];
    }
    public function dS(City $from, City $to, string $date): array
    {
        return [
            "dispatch" => [
                "point" => [
                    "location" => $from->city_id_vozovoz,
                    "address" => "",
                    "date" => $date
                ]
            ],
            "destination" => [
                "point" => [
                    "location" => $to->city_id_vozovoz,
                    "terminal" => "default",
                    "date" => $date
                ]
            ]
        ];
    }
    public function dD(City $from, City $to, string $date): array
    {
        return [
            "dispatch" => [
                "point" => [
                    "location" => $from->city_id_vozovoz,
                    "address" => "",
                    "date" => $date
                ]
            ],
            "destination" => [
                "point" => [
                    "location" => $to->city_id_vozovoz,
                    "address" => "",
                    "date" => $date
                ]
            ]
        ];
    }
}
