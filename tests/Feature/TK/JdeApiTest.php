<?php

namespace Tests\Feature\TK;

use App\Enums\Jde\JdeTariffType;
use App\Enums\Jde\JdeUrlType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class JdeApiTest extends TestCase
{
    /**
     * Проверка корректности работы метода API транспортной компании для расчёта стоимости доставки. 
     */
    public function test_calculate(): void
    {
        $url = config('companies.jde.url') . JdeUrlType::Calculator->value;
        $response = Http::get($url, $this->calculateParameters());

        $this->assertEquals(200, $response->status());
        $this->assertArrayHasKey('price', $response->json());
    }

    /**
     * Проверка корректности работы метода API транспортной компании для получения списка терминалов.
     */
    public function test_geo(): void
    {
        $url = config('companies.jde.url') . JdeUrlType::Geo->value;
        $response = Http::get($url, $this->geoParameters());

        $this->assertEquals(200, $response->status());
        $this->assertIsArray($response->json());
    }

    /**
     * Проверка корректности работы метода API транспортной компании для получения тарифов.
     */
    public function test_type(): void
    {
        $url = config('companies.jde.url') . JdeUrlType::Type->value;
        $response = Http::get($url);

        $this->assertEquals(200, $response->status());
        $this->assertIsArray($response->json());
    }

    private function calculateParameters(): array
    {
        return [
            'from' => 2252083385407018, // Красноярск
            'to' => 2252083358667819, // Москва
            'weight' => 10, // вес (кг)
            'length' => 1, // длина самого габартиного места (м)
            'width' => 0.2, // ширина самого габартиного места (м)
            'height' => 0.1, // высота самого габартиного места (м)
            'volume' => 0.02, // общий объём (м3)
            'quantity' => 1, // количество
            'type' => JdeTariffType::Combined->value, // способ доставки
            'pickup' => 0, // 1 - требуется забор / 0 - нет
            'delivery' => 0, // 1 - требуется доставка / 0 - нет
            'insValue' => 1000, // сумма объявленной ценности
        ];
    }

    private function geoParameters(): array
    {
        return [
            'mode' => 1, // 1 - пункты приёма / 2 - пункты выдачи
        ];
    }
}
