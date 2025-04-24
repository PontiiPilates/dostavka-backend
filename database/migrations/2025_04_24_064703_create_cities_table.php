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
        Schema::create('cities', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('city_id');
            $table->char('country_code', 5);
            $table->char('country_name', 100);
            $table->char('country_fullname', 100);
            $table->integer('region_code');
            $table->char('region_name', 200);
            $table->char('city_code', 50);
            $table->char('city_name', 100);
            $table->char('index_min', 10)->nullable();
            $table->char('index_max', 10)->nullable();
            $table->char('alpha2', 2);
            $table->char('alpha3', 3);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cities');
    }
};
