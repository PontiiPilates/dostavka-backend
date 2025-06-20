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
        Schema::create('terminals_nrg', function (Blueprint $table) {
            $table->id();

            // $table->foreignId('parent_id')->constrained();

            $table->bigInteger('identifier');
            $table->text('name', 100);
            $table->text('description', 100);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('terminals_nrg');
    }
};
