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
        Schema::create('tariffs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('companies_id')->constrained()->comment('транспортная компания');
            $table->char('name', 100)->comment('систменое имя тарифа');
            $table->char('label', 200)->comment('название тарифа');
            $table->char('number', 200)->comment('внешний идентификатор тарифа');
            $table->boolean('sumoc')->nullable()->comment('признак объявленной ценности');
            $table->boolean('sumnp')->nullable()->comment('признак наложенного платежа');
            $table->boolean('international')->nullable()->comment('признак международной доставки');
            $table->boolean('available')->default(true)->comment('признак участия тарифа в калькуляции');
            $table->integer('const_weight')->default(999999)->comment('ограничение веса (гр)');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tariffs');
    }
};
