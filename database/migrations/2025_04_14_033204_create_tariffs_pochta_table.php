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
        Schema::create('tariffs_pochta', function (Blueprint $table) {
            $table->id();

            $table->integer('object')->comment('идентификатор тарифа');
            $table->char('name', 200)->comment('название тарифа');

            $table->boolean('sumoc')->default(false)->comment('с объявленной ценностью');
            $table->boolean('sumnp')->default(false)->comment('с наложенным платежом');

            $table->integer('min_weight')->default(0)->comment('идентификатор тарифа');
            $table->integer('max_weight')->default(1000000000)->comment('идентификатор тарифа');

            $table->boolean('country_to')->default(false)->comment('международное отправление');

            $table->boolean('ss')->default(false)->comment('склад-склад');
            $table->boolean('sd')->default(false)->comment('склад-дверь');
            $table->boolean('ds')->default(false)->comment('дверь-дверь');
            $table->boolean('dd')->default(false)->comment('дверь-склад');

            $table->boolean('available')->default(true)->comment('признак участия тарифа в калькуляции');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tariffs_pochta');
    }
};
