<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLocationStationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('location_stations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('location_id')->index();
            $table->string('station_id',5);
            $table->enum('station_type',array('atmp','sky','wind','sst','wave'))->index();
            $table->tinyInteger('rank');
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
        Schema::dropIfExists('location_stations');
    }
}
