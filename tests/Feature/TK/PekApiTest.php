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

    private function prepare(): void
    {
        $this->url = config('companies.pek.url');
        $this->user = config('companies.pek.user');
        $this->password = config('companies.pek.password');
    }
}
