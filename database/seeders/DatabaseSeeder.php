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
            // TerminalBoxberrySeeder::class, // ! учётная запись заблокирована (необходимо переписать на Яндекс Доставку)
            TerminalDellinSeeder::class,
            TerminalPekSeeder::class,
            TerminalVozovozSeeder::class,
            TerminalDpdSeeder::class,
            TerminalKitSeeder::class,

            // TerminalJdeSeeder::class, // (остаток 132, в таблице 211)
            // TerminalNrgSeeder::class, // должен быть одним из последних, грязная принадлежность к регионам (остаток 4723, в таблице 363)
            // TariffPochtaSeeder::class, // это вообще не локации, просто тарифы почты россии

            // в последнюю очередь
            // локации не имеют типизации, пусть дополняют список, а не образуют его
            // todo: огромное количество - сократить до основных локаций
            TerminalCdekSeeder::class,
        ]);

        // финализация таблицы локаций
        $this->call([
            FinalizerLocationSeeder::class,
        ]);
    }
}
