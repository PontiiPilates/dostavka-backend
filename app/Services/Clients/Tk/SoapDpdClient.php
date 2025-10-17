<?php

namespace App\Services\Clients\Tk;

use App\Enums\Dpd\DpdUrlType;
use SoapClient;

class SoapDpdClient
{
    private string $calculatorUrl;
    private string $geographyUrl;
    private string $clientNumber;
    private string $clientKey;

    public function __construct()
    {
        $url = config('companies.dpd.url');

        $this->calculatorUrl = $url . DpdUrlType::Calculator->value;
        $this->geographyUrl = $url . DpdUrlType::Geography->value;

        $this->clientNumber = config('companies.dpd.client_number');
        $this->clientKey = config('companies.dpd.client_key');
    }

    /**
     * Расчёт стоимости доставки по параметрам посылок.
     * 
     * @param array $parameters
     * @return mixed
     */
    public function serviseCostByParcels2(array $parameters): mixed
    {
        $client = new SoapClient($this->calculatorUrl);

        $response = $client->getServiceCostByParcels2($parameters);

        return $response;
    }

    /**
     * Подразделения без ограничения по габаритам.
     * 
     * @return mixed
     */
    public function terminalsSelfDelivery2(): mixed
    {
        $parameters = [
            'auth' => [
                'clientNumber' => $this->clientNumber,
                'clientKey' => $this->clientKey,
            ]
        ];

        $client = new SoapClient($this->geographyUrl);
        $response = $client->getTerminalsSelfDelivery2($parameters);
        return $response;
    }

    /**
     * Пункты выдачи с ограничениями.
     * 
     * @return mixed
     */
    public function parcelShops(): mixed
    {
        $parameters = [
            'request' => [
                'auth' => [
                    'clientNumber' => $this->clientNumber,
                    'clientKey' => $this->clientKey,
                ]
            ]
        ];

        $client = new SoapClient($this->geographyUrl);
        $response = $client->getParcelShops($parameters);
        return $response;
    }

    /**
     * Города с доставкой наложенным платежом.
     * 
     * @return mixed
     */
    public function citiesCashPay(): mixed
    {
        $parameters = [
            'request' => [
                'auth' => [
                    'clientNumber' => $this->clientNumber,
                    'clientKey' => $this->clientKey,
                ]
            ]
        ];

        $client = new SoapClient($this->geographyUrl);
        $response = $client->getCitiesCashPay($parameters);
        return $response;
    }
}
