<?php

namespace App\Traits;

trait Utilits
{
    /**
     * Преобразует число до трехзначного формата номера.
     */
    public static function numerator($number): string
    {
        switch (strlen($number)) {
            case 1:
                return "00$number";
            case 2:
                return "0$number";
            default:
                return $number;
        }
    }
}
