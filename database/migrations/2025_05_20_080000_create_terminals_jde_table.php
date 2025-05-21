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
        Schema::create('terminals_jde', function (Blueprint $table) {
            $table->id();

            $table->foreignId('city_id')->constrained();
            $table->text('city_name', 100);
            $table->text('terminal_id', 64);
            $table->boolean('acceptance')->default(false)->comment('приём');
            $table->boolean('issue')->default(false)->comment('отправка');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('terminals_jde');
    }
};
