<?php

namespace App\Traits\Tk\Cdek;

use App\Enums\Cdek\CdekUrlType;
use App\Services\Tk\TokenCdekService;
use Exception;
use Illuminate\Support\Facades\Http;

trait SuggestTerminal
{
    public static function terminalIdByLocation($cityName, $countryName, $countryAlpha2): int
    {
        $tokenCdekService = new TokenCdekService();
        $token = $tokenCdekService->getActualToken();

        $url = config('companies.cdek.url') . CdekUrlType::SuggestCities->value;
        $parameters = [
            'name' => $cityName,
            'country_code' => $countryAlpha2,
        ];

        $response = Http::withToken($token)->get($url, $parameters);

        foreach ($response->object() as $item) {

            $matchCity = strstr($item->full_name, $cityName);
            $matchCountry = strstr($item->full_name, $countryName);

            if ($matchCity && $matchCountry) {
                $terminalCode = $item->code;
                break;
            } else {
                throw new Exception("Не удалось найти терминал для запрашиваемой локации.", 404);
            }
        }

        return (int) $terminalCode;
    }
}
