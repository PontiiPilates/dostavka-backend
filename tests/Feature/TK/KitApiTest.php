<?php

namespace Tests\Feature\TK;

use App\Enums\Kit\KitUrlType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class KitApiTest extends TestCase
{
    private string $url;
    private string $token;

    public function test_city(): void
    {
        $this->prepare();

        $response = Http::withToken($this->token)->get($this->url . KitUrlType::City->value);

        $this->assertEquals(200, $response->status());
        $this->assertIsArray($response->json());
    }

    public function test_calculate(): void
    {
        $this->prepare();

        $parameters = [
            "city_pickup_code" => "240000100000", // откуда
            "city_delivery_code" => "770000000000", // куда
            "declared_price" => "100", // объявленная стоимость груза
            "places" => [
                [
                    "count_place" => "2", // количество мест в позиции
                    "height" => "100", // высота см
                    "width" => "100", // ширина см
                    "length" => "100", // длина см
                    "weight" => "100" // вес кг
                ],
                [
                    "count_place" => "4", // количество мест в позиции
                    "weight" => "200", // вес кг
                    "volume" => "4" // объём м3
                ]
            ],
        ];

        $response = Http::withToken($this->token)->post($this->url . KitUrlType::Calculate->value, $parameters);

        $status = $response->status();
        $response = $response->json();

        $this->assertEquals(200, $status);
        $this->assertIsArray($response);
        $this->assertArrayNotHasKey('validate', $response);
    }


    private function prepare(): void
    {
        $this->url = config('companies.kit.url');
        $this->token = config('companies.kit.token');
    }
}
