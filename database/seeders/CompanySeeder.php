<?php

namespace Database\Seeders;

use App\Enums\CompanyType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('companies')->insert([
            'name' => CompanyType::Pochta->value,
            'label' => CompanyType::Pochta->label(),
        ]);

        DB::table('companies')->insert([
            'name' => CompanyType::Baikal->value,
            'label' => CompanyType::Baikal->label(),
        ]);

        DB::table('companies')->insert([
            'name' => CompanyType::DPD->value,
            'label' => CompanyType::DPD->label(),
        ]);
    }
}
