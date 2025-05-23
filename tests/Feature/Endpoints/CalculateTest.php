<?php

namespace Tests\Feature\TK;

use App\Enums\CompanyType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CalculateTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_calculate_jde(): void
    {
        $parameters = $this->parameters(CompanyType::Jde->value);
        $url = route('calculate');

        $firstKey = array_key_first($parameters);
        foreach ($parameters as $key => $parameter) {
            if ($key === $firstKey) {
                $url .= '?' . $key . '=' . $parameter;
            }

            $url .= '&' . $key . '=' . $parameter;
        }

        $response = $this->getJson($url);
        $response->assertStatus(200)->assertJson(['success' => true]);
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
            'companies[]' => $company,
            'delivery_type[]' => 'ss',
            'delivery_type[]' => 'sd',
            'delivery_type[]' => 'ds',
            'delivery_type[]' => 'dd',
            'shipment_date' => now()->isoFormat('YYYY-MM-DD'),
        ];
    }
}
