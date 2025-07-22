<?php

namespace Database\Seeders;

use Database\Seeders\Tk\TerminalBaikalSeeder;
use Database\Seeders\Tk\TerminalBoxberrySeeder;
use Database\Seeders\Tk\TerminalCdekSeeder;
use Database\Seeders\Tk\TerminalDellinSeeder;
use Database\Seeders\Tk\TerminalDpdSeeder;
use Database\Seeders\Tk\TerminalJdeSeeder;
use Database\Seeders\Tk\TerminalKitSeeder;
use Database\Seeders\Tk\TerminalNrgModifySeeder;
use Database\Seeders\Tk\TerminalNrgSeeder;
use Database\Seeders\Tk\TerminalPekSeeder;
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

            CountrySeeder::class,
            // RegionSeeder::class, // засев таблицы выполняет LocationSeeder, необходимо разделить функционал
            LocationSeeder::class,

            TerminalCdekSeeder::class, // очень хороший список населённых пунктов (остаток 638, в таблице 316)
            TerminalDpdSeeder::class, // ёмкий список, но есть свои особенности (остаток 6322, в таблице 390)
            TerminalPekSeeder::class, // (остаток 146, в таблице 709)
            TerminalDellinSeeder::class, // короткий список городов со неприятным уровнем вложенности терминалов (остаток 36, в таблице 182)
            TerminalBoxberrySeeder::class, // список лучше чем Байкал и Кит (остаток 466, в таблице 276)
            TerminalBaikalSeeder::class, // должен быть одним из последних (остаток 12, в таблице 158)
            TerminalKitSeeder::class, // должен быть одним из последних (остаток 27400, в таблице 348)
            TerminalJdeSeeder::class, // (остаток 132, в таблице 211)
            TerminalNrgSeeder::class // должен быть одним из последних, грязная принадлежность к регионам (остаток 4723, в таблице 363)
        ]);
    }
}
