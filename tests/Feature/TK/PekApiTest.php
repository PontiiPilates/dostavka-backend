<?php

namespace Tests\Feature\TK;

use App\Enums\Pek\PekUrlType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PekApiTest extends TestCase
{
    private string $url;
    private string $user;
    private string $password;

    public function test_terminals()
    {
        $this->prepare();

        $response = Http::withBasicAuth($this->user, $this->password)->post($this->url . PekUrlType::Terminals->value);

        $this->assertEquals(200, $response->status());
        $this->assertIsArray($response->json());
    }

    public function test_tariffs()
    {
        $this->prepare();

        $response = Http::withBasicAuth($this->user, $this->password)->post($this->url . PekUrlType::Tariffs->value);

        $this->assertEquals(200, $response->status());
        $this->assertIsArray($response->json());
    }

    public function test_calculate()
    {
        $this->prepare();

        $parameters = [
            "senderWarehouseId" => "36cf9b2c-a415-11dc-a911-000a5e19ccb4", // Красноярск, идентификатор склада отправителя
            "receiverWarehouseId" => "ba335246-2158-11ec-80cf-00155d4a0436", // Москва, идентификатор склада получателя

            "plannedDateTime" => "2025-06-30T00:00:00", // Дата и время планируемой передачи груза (по часовому поясу филиала отправления)

            "isInsurance" => true, // Страхование
            "isInsurancePrice" => 500000, // Стоимость груза (сумма, на которую будет застрахован груз), руб

            "isPickUp" => true, // Нужен забор
            "isDelivery" => true, // Нужна доставка

            "pickup" => [
                "address" => "Россия, Красноярск" // Обязательный параметр для расчёта забора груза.
            ],
            "delivery" => [
                "address" => "Россия, Москва" // Обязательный параметр для расчёта доставки груза.
            ],

            "cargos" => [
                [
                    "weight" => 10.5, // Вес, кг
                    "length" => 1, // Длина груза, м
                    "width" => 0.2, // Ширина груза, м
                    "height" => 0.1, // Высота груза, м
                ],
                [
                    "weight" => 5, // Вес, кг
                    "volume" => 0.1, // Объем груза, м3
                ]
            ]
        ];

        $response = Http::withBasicAuth($this->user, $this->password)->post($this->url . PekUrlType::Calculate->value, $parameters);

        $status = $response->status();
        $data = $response->json();

        $this->assertEquals(200, $status);
        $this->assertIsArray($data);
        $this->assertArrayNotHasKey('error', $data);
        $this->assertEquals(false, $data['hasError']);
    }

    private function prepare(): void
    {
        $this->url = config('companies.pek.url');
        $this->user = config('companies.pek.user');
        $this->password = config('companies.pek.password');
    }
}
