<?php

namespace Tests\Feature\Tk;

use App\Enums\Nrg\NrgUrlType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class NrgApiTest extends TestCase
{
    public function test_cities()
    {
        $token = config('companies.nrg.token');
        $url = config('companies.nrg.url') . NrgUrlType::Cities->value;

        $response = Http::withHeaders(['NrgApi-DevToken' => $token])->get($url);

        $this->assertEquals(200, $response->status());
        $this->assertIsArray($response->json());
        $this->assertArrayHasKey('cityList', $response->json());
    }
}
