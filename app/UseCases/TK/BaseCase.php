<?php

declare(strict_types=1);

namespace App\UseCases\TK;

use App\Models\City;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use SoapClient;

class BaseCase
{
    /**
     * Проверка: является ли доставка международной.
     */
    protected function isInternational($fromCode, $toCode): bool
    {
        if ($fromCode == 643 && $toCode == 643) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Принимает строку в формате: "Город, Страна".
     * Возвращает исчерпывающую информацию о населенном пункте.
     * Эта информация необходима для дальнейшего обеспечения работы интеграции.
     */
    protected function moreInfo($location): City
    {
        $location = str_replace(' ', '', $location);
        $items = explode(',', $location);

        $city = City::where('city_name', $items[0])->where('country_name', $items[1])->first();

        if (!$city) {
            throw new Exception("Некорректное значение места отправления/получения", 404);
        }

        return $city;
    }

    /**
     * SOAP-клиент.
     */
    protected function sendSoap($uri, $dto)
    {
        $client = new SoapClient($uri); // установка подключения SOAP

        try {
            return $client->getServiceCostByParcels2($dto); // отправка запроса
        } catch (\Throwable $th) {
            Log::channel('tk')->error("$uri: " . $th->getMessage(), $dto);
        }
    }

    /**
     * HTTP-клиент для отправки POST-запроса.
     */
    protected function sendPost($url, $dto)
    {
        try {
            return Http::post($url, $dto);
        } catch (\Throwable $th) {
            Log::channel('tk')->error("$url: " . $th->getMessage(), $dto);
        }
    }


    /**
     * 
     */
    protected function successResponse(string $message, array $data = []): array
    {
        return [
            'success' => true,
            'message' => $message,
            'data' => $data,
        ];
    }

    /**
     * 
     */
    protected function errorResponse(string $message, array $data = []): array
    {
        return [
            'success' => false,
            'message' => $message,
            'data' => $data,
        ];
    }
}
