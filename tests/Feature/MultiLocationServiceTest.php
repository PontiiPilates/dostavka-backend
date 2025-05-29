<?php

namespace Tests\Feature;

use App\Services\Location\MultiLocationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class MultiLocationServiceTest extends TestCase
{
    private MultiLocationService $service;

    private function prepare()
    {
        $this->service = new MultiLocationService();
    }

    public function test_city(): void
    {
        $this->prepare();

        $fromCity = 'Красноярск, Россия';
        $toCity = 'Москва, Россия';

        $from = $this->service->city($fromCity, 'from')->toArray();
        $to = $this->service->city($toCity, 'to')->toArray();

        $this->assertIsArray($from);
        $this->assertEquals('Красноярск', $from['city_name']);
        $this->assertIsArray($to);
        $this->assertEquals('Москва', $to['city_name']);
    }

    public function test_special_from_jde(): void
    {
        $this->prepare();

        $fromCity = 'Красноярск, Россия';
        $toCity = 'Москва, Россия';

        $from = $this->service->specialFromJde($fromCity, 'from')->toArray();
        $to = $this->service->specialFromJde($toCity, 'to')->toArray();

        $this->assertIsArray($from);
        $this->assertEquals('Красноярск', $from['city_name']);
        $this->assertIsArray($to);
        $this->assertEquals('Москва', $to['city_name']);
    }
}
