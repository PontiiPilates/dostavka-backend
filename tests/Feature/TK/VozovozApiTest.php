<?php

namespace Tests\Feature\Tk;

use App\Enums\Vozovoz\VozovozUrlType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class VozovozApiTest extends TestCase
{
    private string $url;

    public function test_location(): void
    {
        $this->prepare();

        $template = [
            'object' => VozovozUrlType::Location->value,
            'action' => 'get',
            'params' => array_filter([
                'offset' => 0,
                'limit' => 1,
            ]),
        ];

        $response = Http::post($this->url, $template);

        $this->assertEquals(200, $response->status());
        $this->assertIsArray($response->json());
    }

    public function test_calculate(): void
    {
        $this->prepare();

        $template = [
            "object" => "price",
            "action" => "get",
            "params" => [
                "cargo" => [
                    "insurance" => 50000,               // объявленная стоимость груза
                    "dimension" => [
                        "max" => [
                            "weight" => 15,             // максимальный вес места, м
                            "length" => 0.8,            // максимальная длина места, м
                            "height" => 0.6,            // максимальная высота места, м
                            "width" => 0.4              // максимальная ширина места, м
                        ],
                        "quantity" => 3,                // общее количество мест, шт
                        "volume" => 0.7,                // общий объём, м3
                        "weight" => 20                  // общий вес, кг
                    ]
                ],
                "gateway" => [
                    "dispatch" => [
                        "point" => [
                            "location" => "Красноярск", // откуда
                            "date" => "2025-06-10",
                            "address" => ""
                        ]
                    ],
                    "destination" => [
                        "point" => [
                            "location" => "Москва",     // куда
                            "date" => "2025-06-10",
                            "address" => ""
                        ]
                    ]
                ]
            ]
        ];

        $response = Http::post($this->url, $template);

        $status = $response->status();
        $response = $response->json();

        $this->assertEquals(200, $status);
        $this->assertIsArray($response);
        $this->assertArrayNotHasKey('error', $response);
    }


    private function prepare(): void
    {
        $this->url = config('companies.vozovoz.url') . '?token=' . config('companies.vozovoz.token');
    }
}
