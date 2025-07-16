<?php

namespace Tests\Feature\Tk;

use App\Enums\DPD\DpdUrlType;
use App\Services\Clients\Tk\SoapDpdClient;
use App\Traits\Json;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use function PHPUnit\Framework\assertIsArray;

class DpdApiTest extends TestCase
{
    use Json;

    private SoapDpdClient $client;

    private string $url;
    private string $clientNumber;
    private string $clientKey;

    public function test_citiesCashPay(): void
    {
        $this->dataPrepare();

        $response = $this->client->citiesCashPay();

        assertIsArray($response->return);
    }

    public function test_parcelShops(): void
    {
        $this->dataPrepare();

        $response = $this->client->parcelShops();

        assertIsArray($response->return->parcelShop);
    }

    public function test_terminalsSelfDelivery2(): void
    {
        $this->dataPrepare();

        $response = $this->client->terminalsSelfDelivery2();

        assertIsArray($response->return->terminal);
    }

    private function dataPrepare()
    {
        $this->client = new SoapDpdClient();

        $this->url = config('companies.dpd.url') . DpdUrlType::Geography->value;
        $this->clientNumber = config('companies.dpd.client_number');
        $this->clientKey = config('companies.dpd.client_key');
    }
}
