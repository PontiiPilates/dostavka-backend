<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('terminals_kit', function (Blueprint $table) {
            $table->id();

            $table->foreignId('location_id')->nullable()->constrained();

            $table->char('identifier', 50)->nullable()->comment('идентификатор терминала/локации');
            $table->text('name', 100)->comment('название локации');
            $table->text('type', 100)->nullable()->comment('тип локации');
            $table->text('district', 100)->nullable()->comment('название района');
            $table->text('region', 100)->nullable()->comment('название региона');
            $table->boolean('federal')->default(false)->comment('значимая территория');
            $table->text('country', 10)->nullable()->comment('страна');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('terminals_kit');
    }
};
