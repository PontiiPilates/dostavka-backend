<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TariffSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pochta = Company::where('name', 'pochta')->first();

        DB::table('tariffs')->insert([
            'companies_id' => $pochta->id,
            'name' => 'online_package',
            'label' => 'Посылка онлайн обыкновенная',
            'number' => '23030',
            'sumoc' => false,
            'sumnp' => false,
        ]);

        DB::table('tariffs')->insert([
            'companies_id' => $pochta->id,
            'name' => 'online_package_with_sumoc',
            'label' => 'Посылка онлайн с объявленной ценностью',
            'number' => '23020',
            'sumoc' => true,
            'sumnp' => false,
        ]);

        DB::table('tariffs')->insert([
            'companies_id' => $pochta->id,
            'name' => 'online_package_with_sumoc_and_cash',
            'label' => 'Посылка онлайн с объявленной ценностью и наложенным платежом',
            'number' => '23040',
            'sumoc' => true,
            'sumnp' => true,
        ]);
    }
}
