<?php

namespace Database\Seeders;

use App\Enums\Cdek\CdekUrlType;
use App\Traits\TokenCdek;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class TokenCdekSeeder extends Seeder
{
    use TokenCdek;

    public function run(): void
    {
        DB::table('token_cdek')->insert([
            'token' => $this->getNewToken()->access_token,
            'expires' => $this->getNewToken()->expires_in,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
