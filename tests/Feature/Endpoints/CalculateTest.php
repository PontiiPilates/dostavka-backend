<?php

namespace Tests\Feature\TK;

use App\Enums\CompanyType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CalculateTest extends TestCase
{
    public function test_calculate_kit(): void
    {
        $parameters = $this->parameters(CompanyType::Kit->value);

        // удаление информации о наложенном платеже
        // данная компания с ним не работает
        unset($parameters['cash_on_delivery']);

        $url = $this->toUrl($parameters);

        $response = $this->getJson($url);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertArrayNotHasKey('success', $response['data'][0]['kit']);
    }

    public function test_calculate_jde(): void
    {
        $parameters = $this->parameters(CompanyType::Jde->value);
        $url = $this->toUrl($parameters);

        $response = $this->getJson($url);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertArrayNotHasKey('success', $response['data'][0]['jde']);
    }

    public function test_calculate_pek(): void
    {
        $parameters = $this->parameters(CompanyType::Pek->value);
        $url = $this->toUrl($parameters);

        $response = $this->getJson($url);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $response->assertJsonStructure($this->responseStructure());
    }

    private function parameters(string $company): array
    {
        return [
            'from' => 'Красноярск, Россия',
            'to' => 'Москва, Россия',

            'places[0][weight]' => '10',
            'places[0][length]' => '100',
            'places[0][width]' => '20',
            'places[0][height]' => '10',
            'places[0][volume]' => '0.2',

            'places[1][weight]' => '20',
            'places[1][volume]' => '0.4',

            'companies[]' => $company,

            'delivery_type[]' => 'ss',
            'delivery_type[]' => 'dd',

            'declare_price' => 20000,
            'cash_on_delivery' => 1000,

            'shipment_date' => now()->addDays(1)->isoFormat('YYYY-MM-DD'),
        ];
    }

    private function responseStructure()
    {
        return [
            "data" => [
                "*" => [
                    "pek" => [
                        "dd" => [
                            "*" => [
                                "tariff",
                                "cost",
                                "days",
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    private function toUrl(array $parameters): string
    {
        $url = route('calculate');

        $firstKey = array_key_first($parameters);
        foreach ($parameters as $key => $parameter) {
            if ($key === $firstKey) {
                $url .= '?' . $key . '=' . $parameter;
                continue;
            }

            $url .= '&' . $key . '=' . $parameter;
        }

        return $url;
    }
}
