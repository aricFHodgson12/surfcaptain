<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLocationForecastsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('location_forecasts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('location_beach_id')->unique();
            $table->integer('location_id')->unique();
            $table->text('timeline');
            $table->text('summary');
            $table->text('tide_sunrise');
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
        Schema::dropIfExists('location_forecasts');
    }
}
