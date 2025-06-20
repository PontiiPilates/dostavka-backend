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
        Schema::create('tk_pek_terminals', function (Blueprint $table) {
            $table->id();

            $table->foreignId('location_id')->constrained();
            $table->text('city_name', 100);
            $table->text('terminal_id', 64);
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
        Schema::dropIfExists('tk_pek_terminals');
    }
};
