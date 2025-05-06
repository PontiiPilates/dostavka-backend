<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const COLUMN = 'city_id_vozovoz';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cities', function (Blueprint $table) {
            $table->char(self::COLUMN, 50)->nullable()->comment('Идентификатор города Vozovoz');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cities', function (Blueprint $table) {
            $table->dropColumn(self::COLUMN);
        });
    }
};
