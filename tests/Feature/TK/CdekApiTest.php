<?php

namespace Tests\Feature\Tk;

use App\Enums\Cdek\CdekUrlType;
use App\Services\Tk\TokenCdekService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

use function PHPUnit\Framework\assertIsInt;
use function PHPUnit\Framework\assertIsString;

class CdekApiTest extends TestCase
{
    public function test_auth(): void
    {
        $tokenCdecService = new TokenCdekService();
        $token = $tokenCdecService->getNewToken();

        assertIsString($token->access_token);
        assertIsInt($token->expires_in);
    }

    public function test_cities()
    {
        $tokenCdecService = new TokenCdekService();
        $token = $tokenCdecService->getActualToken();

        $response = Http::withToken($token)->get($tokenCdecService->url . CdekUrlType::Cities->value);

        dump($token);

        $this->assertEquals(200, $response->status());
        $this->assertIsArray($response->json());
        $this->assertArrayNotHasKey('error', $response);
    }
}
