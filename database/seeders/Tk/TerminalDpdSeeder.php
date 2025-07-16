<?php

namespace Database\Seeders\Tk;

use App\Enums\DPD\DpdUrlType;
use App\Models\Location;
use App\Models\Tk\TerminalDpd;
use App\Services\XML\XmlParser;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class TerminalDpdSeeder extends Seeder
{
    private string $url;
    private string $clientNumber;
    private string $clientKey;

    private array $candidatsToUpdate = [];

    public function run(): void
    {
        // подготовка данных
        $this->url = config('companies.dpd.url') . DpdUrlType::Geography->value;
        $this->clientNumber = config('companies.dpd.client_number');
        $this->clientKey = config('companies.dpd.client_key');

        $citiesCashPay = 'assets\tk\dpd\cities_cash_pay.xml';
        $parcelShopsRequest = 'assets\tk\dpd\parcel_shops_request.xml';
        $terminalsSelfDelivery2 = 'assets\tk\dpd\terminals_self_delivery_2.xml';

        // обновление/создание файлов
        $response = $this->send($this->citiesCashPayRequest());
        Storage::put($citiesCashPay, $response);

        $response = $this->send($this->parcelShopsRequest());
        Storage::put($parcelShopsRequest, $response);

        $response = $this->send($this->terminalsSelfDelivery2Request());
        Storage::put($terminalsSelfDelivery2, $response);

        // парсинг файлов
        $xmlParser = new XmlParser();
        $dataCitiesCashPay = $xmlParser->dpdCitiesCashPay($citiesCashPay);
        $dataParcelShops = $xmlParser->dpdParcelShops($parcelShopsRequest);
        $dataTerminalsSelfDelivery2 = $xmlParser->dpdTerminalsSelfDelivery2($terminalsSelfDelivery2);

        // засев данными
        $this->seed($dataCitiesCashPay);
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
    private function send($request): string
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

        curl_close($curl);

        return $response;
    }

    private function seed($data)
    {
        // особенностью данной тк является указание регионов, в котором отсутствует принадлежность к краю, области, республике и т.д.
        // что касается самого списка, то он вполне ёмкий и системмный
        foreach ($data as $city) {
            $city = (object) $city;

            $location = Location::query()
                ->where('name', $city->cityName)
                ->whereHas('country', function ($query) use ($city) {
                    $query->where('alpha2', $city->countryCode);
                })->first();

            // если локация не обнаружена, то она попадает в список кандидатов на парсинг
            if (!$location) {
                $this->candidatsToUpdate[] = $city->abbreviation . '. ' . $city->cityName . ': ' . $city->regionName . ', ' . $city->regionCode;
                continue;
            }

            TerminalDpd::create([
                'location_id' => $location->id,
                'identifier' => $city->cityId,
                'name' => $city->cityName,
                'dirty' => $city->abbreviation . '. ' . $city->cityName . ': ' . $city->regionName . ', ' . $city->regionCode,
            ]);
        }

        dump('Следующие локации остались не добавленными: ', $this->candidatsToUpdate);
    }
}
