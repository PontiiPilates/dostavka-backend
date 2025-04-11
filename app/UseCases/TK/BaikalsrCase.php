<?php

declare(strict_types=1);

namespace App\UseCases\TK;

use Illuminate\Support\Facades\Http;

class BaikalsrCase extends BaseCase
{
    public function handle()
    {
        // $response = Http::
        // withBasicAuth("d585dd0f7a2149c6ce3632cb68f1f729", "")
        // ->withHeaders(['Content-Type' => 'application/json'])
        // withHeaders(['Authorization' => base64_encode("310f294c65b487902b93a6d73f45e16d:45372")])
        // withHeaders(['Authorization' => 'Basic ' . base64_encode("310f294c65b487902b93a6d73f45e16d:")])
        // ->post('https://test-api.baikalsr.ru/v2/fias/cities?text=<Красноярск>');
        // dd($response);
        // return $response;
    }
}
