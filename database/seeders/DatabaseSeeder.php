<?php

namespace Database\Seeders;

use Database\Seeders\Tk\TerminalBaikalSeeder;
use Database\Seeders\Tk\TerminalBoxberrySeeder;
use Database\Seeders\Tk\TerminalCdekSeeder;
use Database\Seeders\Tk\TerminalDellinSeeder;
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
            TerminalCdekSeeder::class, // очень хороший список населённых пунктов (остаток 638, в таблице 316)
            // TerminalsJdeSeeder::class,
            // TkKitCitySeeder::class,
            // TkPekTerminalSeeder::class,
            // TerminalNrgSeeder::class,
            TerminalNrgModifySeeder::class, // (остаток 392, в таблице 0)
            // TerritorySeeder::class
            TerminalDellinSeeder::class, // короткий список городов со неприятным уровнем вложенности терминалов (остаток 36, в таблице 182)
            TerminalBoxberrySeeder::class, // список лучше чем Байкал и Кит (остаток 466, в таблице 276)
            TerminalBaikalSeeder::class, // должен быть одним из последних (остаток 12, в таблице 158)
            TerminalKitSeeder::class, // должен быть одним из последних (остаток 27400, в таблице 348)
        ]);
    }
}
