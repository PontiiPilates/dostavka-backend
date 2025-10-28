<?php

namespace App\DTO;

final class CalculationResultDto
{
    public static function top($request, $from, $to): array
    {
        return [
            'origin' => $request->all(),        // оригинальный запрос
            'direction' => [
                'from' => $from,                // откуда
                'to' => $to                     // куда
            ],
            'results' => self::empty($request), // результаты калькуляции по компаниям
            'begin' => now(),                   // время создания структуры
            'complete' => null,                 // время завершения сборки структуры
            'is_complete' => false,             // отметка о завершенности
            'timeout' => 20,                    // время, по истечении которого считать транзакции завершенными
        ];
    }

    private static function empty($request): array
    {
        $data = [];
        foreach ($request->companies as $company) {
            $data[$company] = [
                'success' => [],        // контейнер для результатов калькуляции
                'errors' => [],         // контейнер для ошибок
                'is_complete' => false  // отметка о завершенности по конкретной компании
            ];
        }

        return $data;
    }

    public static function filler($company): array
    {
        return [
            'company' => $company,  // название компании
            'data' => [
                'success' => [],    // контейнер для результатов калькуляции
                'errors' => [],     // контейнер для ошибок
            ],
            'is_complete' => true   // отметка о завершенности по конкретной компании
        ];
    }

    public static function tariff($name, $cost, $from, $to): array
    {
        return [
            "tariff" => $name,      // наименование тарифа
            "cost" => $cost,        // стоимость транспортировки
            "days" => [
                "from" => $from,    // от, дней
                "to" => $to,        // до, дней
            ],
        ];
    }
}
