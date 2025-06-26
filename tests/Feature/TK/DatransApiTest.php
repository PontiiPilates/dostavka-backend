<?php

namespace Tests\Feature\Tk;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DatransApiTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_calc()
    {
		$hash = md5('superKeyDT001' . "krasnoyarsk" . "moskva");

        dd($hash);
        // $username = config('companies.baikal.username');
        // $url = config('companies.baikal.url') . BaikalUrlType::Affiliate->value;

        // $response = Http::withBasicAuth($username, '')->get($url);

        // $this->assertEquals(200, $response->status());
        // $this->assertIsArray($response->json());
    }
}
