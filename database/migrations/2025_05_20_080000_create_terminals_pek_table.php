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
        Schema::create('terminals_pek', function (Blueprint $table) {
            $table->id();

            // во время формирования таблицы может быть null
            // заполнение происходит после формирования таблицы locations
            $table->foreignId('location_id')->nullable()->constrained();

            $table->char('identifier', 50)->comment('идентификатор локации');
            $table->text('name', 100)->comment('название локации');
            $table->text('type', 100)->nullable()->comment('тип локации локации');
            $table->text('district', 100)->nullable()->comment('название района');
            $table->text('region', 100)->nullable()->comment('название региона');
            $table->boolean('federal')->default(false)->comment('значимая территория');
            $table->text('country', 10)->nullable()->comment('страна');

            $table->float('max_weight')->default(0)->comment('максимальный вес груза');
            $table->float('max_volume')->default(0)->comment('максимально допустимый объем груза');
            $table->float('max_weight_per_place')->default(0)->comment('максимально допустимый вес грузоместа');
            $table->float('max_dimension')->default(0)->comment('максимальный габарит грузоместа');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('terminals_pek');
    }
};
