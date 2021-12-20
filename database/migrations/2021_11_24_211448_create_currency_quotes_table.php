<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCurrencyQuotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('currency_quotes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_from')->default(0);
            $table->unsignedBigInteger('id_to')->default(0);
            $table->float('bid')->default(0);
            $table->float('ask')->default(0);
            $table->foreign('id_from')->references('id')->on('currencies');
            $table->foreign('id_to')->references('id')->on('currencies');
            $table->index(['id_from', 'id_to']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('currency_quotes');
    }
}
