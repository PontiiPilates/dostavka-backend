<?php

namespace Tests\Feature\Tk;

use App\Enums\DPD\DpdUrlType;
use App\Traits\Json;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use stdClass;
use Tests\TestCase;

use function PHPUnit\Framework\assertEquals;

class DpdApiTest extends TestCase
{
    use Json;

    private string $url;
    private string $clientNumber;
    private string $clientKey;

    public function test_citiesCashPay(): void
    {
        $this->dataPrepare();

        $response = $this->send($this->citiesCashPayRequest());

        assertEquals(200, $response->info->http_code);
        assertEquals('string', gettype($response->body));
    }

    public function test_parcelShops(): void
    {
        $this->dataPrepare();

        $response = $this->send($this->parcelShopsRequest());

        assertEquals(200, $response->info->http_code);
        assertEquals('string', gettype($response->body));
    }

    public function test_terminalsSelfDelivery2(): void
    {
        $this->dataPrepare();

        $response = $this->send($this->terminalsSelfDelivery2Request());

        assertEquals(200, $response->info->http_code);
        assertEquals('string', gettype($response->body));
    }

    private function dataPrepare()
    {
        $this->url = config('companies.dpd.url') . DpdUrlType::Geography->value;
        $this->clientNumber = config('companies.dpd.client_number');
        $this->clientKey = config('companies.dpd.client_key');
    }

    private function citiesCashPayRequest()
    {
        return "<soapenv:Envelope xmlns:soapenv='http://schemas.xmlsoap.org/soap/envelope/' xmlns:ns='http://dpd.ru/ws/geography/2015-05-20'>
                    <soapenv:Header/>
                    <soapenv:Body>
                        <ns:getCitiesCashPay>
                            <request>
                                <auth>
                                    <clientNumber>$this->clientNumber</clientNumber>
                                    <clientKey>$this->clientKey</clientKey>
                                    </auth>
                                    </request>
                        </ns:getCitiesCashPay>
                        </soapenv:Body>
                        </soapenv:Envelope>";
    }

    private function parcelShopsRequest()
    {
        return "<soapenv:Envelope xmlns:soapenv='http://schemas.xmlsoap.org/soap/envelope/' xmlns:ns='http://dpd.ru/ws/geography/2015-05-20'>
                    <soapenv:Header/>
                    <soapenv:Body>
                        <ns:getParcelShops>
                            <request>
                                <auth>
                                    <clientNumber>$this->clientNumber</clientNumber>
                                    <clientKey>$this->clientKey</clientKey>
                                </auth>
                            </request>
                        </ns:getParcelShops>
                    </soapenv:Body>
                </soapenv:Envelope>";
    }

    private function terminalsSelfDelivery2Request()
    {
        return "<soapenv:Envelope xmlns:soapenv='http://schemas.xmlsoap.org/soap/envelope/' xmlns:ns='http://dpd.ru/ws/geography/2015-05-20'>
                    <soapenv:Header/>
                    <soapenv:Body>
                        <ns:getTerminalsSelfDelivery2>
                            <auth>
                                <clientNumber>$this->clientNumber</clientNumber>
                                <clientKey>$this->clientKey</clientKey>
                            </auth>
                        </ns:getTerminalsSelfDelivery2>
                    </soapenv:Body>
                </soapenv:Envelope>";
    }

    /**
     * Потребность в Curl обусловлена тем, что Guzzle возвращает ошибку при получении ответа.
     */
    private function send($request): stdClass
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $request,
            CURLOPT_HTTPHEADER => array('Content-Type: text/xml'),
        ));

        $response = curl_exec($curl);
        $info = curl_getinfo($curl);

        curl_close($curl);

        return (object) [
            'body' => $response,
            'info' => (object) $info,
        ];
    }
}
