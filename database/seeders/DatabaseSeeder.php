<?php

namespace Database\Seeders;

use Database\Seeders\Tk\TariffPochtaSeeder;
use Database\Seeders\Tk\TerminalBaikalSeeder;
use Database\Seeders\Tk\TerminalCdekSeeder;
use Database\Seeders\Tk\TerminalDellinSeeder;
use Database\Seeders\Tk\TerminalDpdSeeder;
use Database\Seeders\Tk\TerminalJdeSeeder;
use Database\Seeders\Tk\TerminalKitSeeder;
use Database\Seeders\Tk\TerminalNrgSeeder;
use Database\Seeders\Tk\TerminalPekSeeder;
use Database\Seeders\Tk\TerminalVozovozSeeder;
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
        ]);

        // посев первичных данных
        $this->call([
            CountrySeeder::class,
            RegionSeeder::class,
            LocationSeeder::class,
        ]);

        // посев тарифов компаний
        $this->call([
            TariffPochtaSeeder::class,
        ]);

        // посев терминалов компаний
        $this->call([
            TerminalBaikalSeeder::class, // 140
            TerminalDellinSeeder::class, // 219
            TerminalJdeSeeder::class, // 282
            TerminalPekSeeder::class, // 743
            TerminalNrgSeeder::class, // 4 801
            TerminalDpdSeeder::class, // 6 098 + индексы для почты россии
            TerminalKitSeeder::class, // 25 312
            TerminalCdekSeeder::class, // 84 307
            TerminalVozovozSeeder::class, // 184 437
        ]);

        // постороение таблицы локаций
        $this->call([
            FinalizerLocationSeeder::class,
        ]);
    }
}
