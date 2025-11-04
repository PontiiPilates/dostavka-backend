<?php

namespace Database\Seeders;

use Database\Seeders\Tk\TariffPochtaSeeder;
use Database\Seeders\Tk\TerminalBaikalSeeder;
use Database\Seeders\Tk\TerminalBoxberrySeeder;
use Database\Seeders\Tk\TerminalCdekSeeder;
use Database\Seeders\Tk\TerminalDellinSeeder;
use Database\Seeders\Tk\TerminalDpdSeeder;
use Database\Seeders\Tk\TerminalJdeSeeder;
use Database\Seeders\Tk\TerminalKitSeeder;
use Database\Seeders\Tk\TerminalNrgSeeder;
use Database\Seeders\Tk\TerminalPekSeeder;
use Database\Seeders\Tk\TerminalVozovozSeeder;
use Database\Seeders\Tk\TerritoriesDellinSeeder;
use Database\Seeders\Tk\TerritoriesPekSeeder;
use Database\Seeders\Tk\TerritoriesVozovozSeeder;
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

        // засев начальными (эталонными) данными
        $this->call([
            CountrySeeder::class,
            RegionSeeder::class,
            LocationSeeder::class,
        ]);

        // засев терминалов компаний
        $this->call([
            TerminalBaikalSeeder::class,
            TerminalDellinSeeder::class,
            TerminalPekSeeder::class,
            TerminalVozovozSeeder::class,
            TerminalDpdSeeder::class,
            TerminalKitSeeder::class, // дополняет базу локаций/регионов, грязные типы/региональность
            TerminalNrgSeeder::class, // регистрирует собственные + дополняет в незначительной степени (нет типов, грязная региональность)

            // TariffPochtaSeeder::class, // это вообще не локации, просто тарифы почты россии

            TerminalCdekSeeder::class, // todo: скорректировать на: только регистрирует собственные, нет типов, бигдата
            TerminalJdeSeeder::class, // регистрирует собственные + дополняет в незначительной степени (нет типов, грязная региональность)
        ]);

        // финализация таблицы локаций
        $this->call([
            FinalizerLocationSeeder::class,
        ]);
    }
}
