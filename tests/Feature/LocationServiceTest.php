<?php

namespace Tests\Feature;

use App\Services\LocationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LocationServiceTest extends TestCase
{
    private LocationService $service;

    private string $fromCity;
    private string $toCity;

    private function prepare()
    {
        $this->service = new LocationService();

        $this->fromCity = 'Красноярск, Россия';
        $this->toCity = 'Москва, Россия';
    }

    public function test_city(): void
    {
        $this->prepare();

        $from = $this->service->city($this->fromCity, 'from')->toArray();
        $to = $this->service->city($this->toCity, 'to')->toArray();

        $this->assertIsArray($from);
        $this->assertEquals('Красноярск', $from['city_name']);
        $this->assertIsArray($to);
        $this->assertEquals('Москва', $to['city_name']);
    }

    public function test_tkJde(): void
    {
        $this->prepare();

        $from = $this->service->specialFromJde($this->fromCity, 'from')->toArray();
        $to = $this->service->specialFromJde($this->toCity, 'to')->toArray();

        $this->assertIsArray($from);
        $this->assertEquals('Красноярск', $from['city_name']);
        $this->assertIsArray($to);
        $this->assertEquals('Москва', $to['city_name']);
    }

    public function test_tkPek(): void
    {
        $this->prepare();

        $cargo = [
            'maxWeight' => 100, // кг
            'maxVolume' => 0.9, // м3
            'maxWeightPerPlace' => 60, // кг
            'maxDimension' => 2.5, // м
        ];

        $from = $this->service->tkPek($this->fromCity, $cargo)->toArray();
        $to = $this->service->tkPek($this->toCity, $cargo)->toArray();

        $this->assertIsArray($from);
        $this->assertEquals('Красноярск', $from['city_name']);
        $this->assertIsArray($to);
        $this->assertEquals('Москва', $to['city_name']);
    }
}
