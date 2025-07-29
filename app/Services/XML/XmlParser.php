<?php

namespace App\Services\XML;

use DOMDocument;
use DOMXPath;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class XmlParser
{
    /**
     * Возвращает список городов с возможностью доставки наложенным платежом.
     */
    public function dpdCitiesCashPay(string $pathToFile): Collection
    {
        $xmlString = Storage::get($pathToFile);

        $dom = new DOMDocument();
        $dom->loadXML($xmlString);

        $dom->xinclude();
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('S', 'http://schemas.xmlsoap.org/soap/envelope/');
        $xpath->registerNamespace('ns2', 'http://dpd.ru/ws/geography/2015-05-20');

        $returns = $xpath->query('//S:Envelope/S:Body/ns2:getCitiesCashPayResponse/return');

        $cities = collect();

        foreach ($returns as $return) {
            $city = [
                'cityId' => $xpath->evaluate('string(cityId)', $return),
                'countryCode' => $xpath->evaluate('string(countryCode)', $return),
                'countryName' => $xpath->evaluate('string(countryName)', $return),
                'regionCode' => $xpath->evaluate('string(regionCode)', $return),
                'regionName' => $xpath->evaluate('string(regionName)', $return),
                'cityCode' => $xpath->evaluate('string(cityCode)', $return),
                'cityName' => $xpath->evaluate('string(cityName)', $return),
                'abbreviation' => $xpath->evaluate('string(abbreviation)', $return),
                'indexMin' => $xpath->evaluate('string(indexMin)', $return),
                'indexMax' => $xpath->evaluate('string(indexMax)', $return),
            ];

            $cities->push($city);
        }

        return $cities;
    }

    /**
     * Возвращает список пунктов с ограничениями по габаритам, весу, доступностью самовывозв/самопривоза.
     */
    public function dpdParcelShops(string $pathToFile): Collection
    {
        $xmlString = Storage::get($pathToFile);

        $dom = new DOMDocument();
        $dom->loadXML($xmlString);

        $dom->xinclude();
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('S', 'http://schemas.xmlsoap.org/soap/envelope/');
        $xpath->registerNamespace('ns2', 'http://dpd.ru/ws/geography/2015-05-20');

        $returns = $xpath->query('//S:Envelope/S:Body/ns2:getParcelShopsResponse/return');

        $cities = collect();

        foreach ($returns as $return) {

            $parcelShop = $xpath->query('//parcelShop');

            foreach ($parcelShop as $item) {

                $city = [
                    'code' => $xpath->evaluate('string(code)', $item),
                ];
            }

            $cities->push($city);
        }

        return $cities;
    }

    /**
     * Возвращает список подразделений не имеющих ограничений по габаритам и весу.
     */
    public function dpdTerminalsSelfDelivery2(string $pathToFile): Collection
    {
        $xmlString = Storage::get($pathToFile);

        $dom = new DOMDocument();
        $dom->loadXML($xmlString);

        $dom->xinclude();
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('S', 'http://schemas.xmlsoap.org/soap/envelope/');
        $xpath->registerNamespace('ns2', 'http://dpd.ru/ws/geography/2015-05-20');

        $returns = $xpath->query('//S:Envelope/S:Body/ns2:getTerminalsSelfDelivery2Response/return');

        $cities = collect();

        foreach ($returns as $return) {
            $city = [
                'cityId' => $xpath->evaluate('string(cityId)', $return),
                'countryCode' => $xpath->evaluate('string(countryCode)', $return),
                'countryName' => $xpath->evaluate('string(countryName)', $return),
                'regionCode' => $xpath->evaluate('string(regionCode)', $return),
                'regionName' => $xpath->evaluate('string(regionName)', $return),
                'cityCode' => $xpath->evaluate('string(cityCode)', $return),
                'cityName' => $xpath->evaluate('string(cityName)', $return),
                'abbreviation' => $xpath->evaluate('string(abbreviation)', $return),
            ];

            $cities->push($city);
        }

        return $cities;
    }
}
