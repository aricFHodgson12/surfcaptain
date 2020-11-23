<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveColumnsFromLocationForecastParamsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('location_forecast_params', function (Blueprint $table) {
            $table->dropColumn(['nam_hires_id', 'nam_conus_id', 'nws_id','sst_id','gfs_id','buoy_id','nearshore_lat','nearshore_lon','ww3_bull_name','bathymetry']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('location_forecast_params', function (Blueprint $table) {
            $table->integer('nam_hires_id');
            $table->integer('nam_conus_id');
            $table->integer('nws_id');
            $table->integer('sst _id');
            $table->integer('gfs_id');
            $table->char('buoy_id',5);
            $table->float('nearshore_lat',5,3);
            $table->float('nearshore_lon',6,3);
            $table->char('ww3_bull_name',60);
        });
    }
}
