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
        Schema::create('terminals_boxberry', function (Blueprint $table) {
            $table->id();

            $table->foreignId('location_id')->constrained();

            $table->char('identifier', 50)->comment('идентификатор локации');
            $table->text('name', 100)->comment('название локации');
            $table->text('dirty', 300)->nullable()->comment('данные о регионе локации');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('terminals_boxberry');
    }
};
