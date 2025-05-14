<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const CITY = 'city_code_dellin';
    private const TERMINAL = 'terminal_id_dellin';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cities', function (Blueprint $table) {
            $table->char(self::CITY, 50)->nullable()->comment('Идентификатор города Dellin');
            $table->char(self::TERMINAL, 50)->nullable()->comment('Идентификатор терминала Dellin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cities', function (Blueprint $table) {
            $table->dropColumn(self::CITY);
            $table->dropColumn(self::TERMINAL);
        });
    }
};
