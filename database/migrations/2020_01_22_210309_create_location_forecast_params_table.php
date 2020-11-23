<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLocationForecastParamsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('location_beaches', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->boolean('nam_data');
            $table->integer('beach_face_dir');
            $table->char('buoy_id',5);
            $table->integer('swell_window_left');
            $table->integer('swell_window_right');
            $table->integer('wind_block');
            $table->float('nearshore_lat',5,3);
            $table->float('nearshore_lon',6,3);
            $table->char('ww3_grid',10);
            $table->float('ww3_lat',5,3);
            $table->float('ww3_lon',6,3);
            $table->tinyInteger('rank')->index();
            $table->char('slug',30)->unique();
            $table->tinyInteger('active')->index();
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
        Schema::dropIfExists('location_forecast_params');
    }
}
