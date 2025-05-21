<?php

namespace Database\Seeders;

use Database\Seeders\Tk\TerminalsJdeSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CompanySeeder::class,
            TariffPochtaSeeder::class,
            CountrySeeder::class,
            TerminalsJdeSeeder::class
        ]);
    }
}
