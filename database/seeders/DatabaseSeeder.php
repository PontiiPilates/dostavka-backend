<?php

namespace Database\Seeders;

use Database\Seeders\Tk\TerminalBaikalSeeder;
use Database\Seeders\Tk\TerminalBoxberrySeeder;
use Database\Seeders\Tk\TerminalCdekSeeder;
use Database\Seeders\Tk\TerminalKitSeeder;
use Database\Seeders\Tk\TerminalNrgModifySeeder;
use Database\Seeders\Tk\TerminalNrgSeeder;
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
            // CompanySeeder::class,
            // TariffPochtaSeeder::class,
            CountrySeeder::class,
            // RegionSeeder::class,
            LocationSeeder::class,
            // TerminalsJdeSeeder::class,
            // TkKitCitySeeder::class,
            // TkPekTerminalSeeder::class,
            // TerminalCdekSeeder::class,
            // TerminalNrgSeeder::class,
            TerminalNrgModifySeeder::class,
            // TerritorySeeder::class
            TerminalBoxberrySeeder::class, // список лучше чем Байкал и Кит
            TerminalBaikalSeeder::class, // должен быть одним из последних
            TerminalKitSeeder::class, // должен быть одним из последних
        ]);
    }
}
