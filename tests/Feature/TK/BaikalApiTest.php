<?php

namespace Tests\Feature\TK;

use App\Enums\Baikal\BaikalUrlType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BaikalApiTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_cities()
    {
        $username = config('companies.baikal.username');
        $url = config('companies.baikal.url') . BaikalUrlType::Affiliate->value;

        $response = Http::withBasicAuth($username, '')->get($url);

        $this->assertEquals(200, $response->status());
        $this->assertIsArray($response->json());
    }
}
