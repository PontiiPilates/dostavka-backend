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
        Schema::create('territories', function (Blueprint $table) {
            $table->id();

            $table->integer('code')->nullable();
            $table->string('name', 100)->nullable();
            $table->string('fullname', 100)->nullable();
            $table->string('alpha2', 10)->nullable();
            $table->string('alpha3', 10)->nullable();

            $table->string('row_region_name', 100)->nullable;
            $table->string('public_region_name', 100)->nullable();

            $table->string('location_name', 100)->nullable();
            $table->string('location_type', 100)->nullable();


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('territories');
    }
};
