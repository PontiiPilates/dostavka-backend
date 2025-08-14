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

            // засев начальными (эталонными) данными
            CountrySeeder::class,
            RegionSeeder::class,
            LocationSeeder::class,

            // добавляет 100к населённых пунктов
            TerminalVozovozSeeder::class,
            TerritoriesVozovozSeeder::class,

            // добавляет 850 населенных пунктов
            TerminalPekSeeder::class,
            TerritoriesPekSeeder::class,

            // TerminalDellinSeeder::class, // короткий список городов со неприятным уровнем вложенности терминалов (остаток 36, в таблице 182)
            // TerminalBoxberrySeeder::class, // список лучше чем Байкал и Кит (остаток 466, в таблице 276)
            // TerminalBaikalSeeder::class, // должен быть одним из последних (остаток 12, в таблице 158)
            // TerminalKitSeeder::class, // должен быть одним из последних (остаток 27400, в таблице 348)
            // TerminalJdeSeeder::class, // (остаток 132, в таблице 211)
            // TerminalNrgSeeder::class, // должен быть одним из последних, грязная принадлежность к регионам (остаток 4723, в таблице 363)

            // у данной тк хороший, системный список, его данные близки к эталонным и ложатся в основу построения гео-данных
            // данный сидер должен запускаться в первую очередь
            // дополняет данные 63232 элементами
            // TerminalDpdSeeder::class,

            // у данной тк приемлемый список
            // однако данные списка регионов расходятся со списком городов в части наименования регионов
            // TerminalCdekSeeder::class,

            // TariffPochtaSeeder::class, // ото вообще не локации, просто тарифы почты россии
        ]);
    }
}
