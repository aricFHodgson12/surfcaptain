<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddParamColumnsToLocationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->char('buoy_id',5);
            $table->float('nearshore_lat',5,3);
            $table->float('nearshore_lon',6,3);
            $table->enum('bathymetry',array('low','normal','high'));
            $table->char('ww3_bull_name',60);
            $table->integer('nam_hires_id');
            $table->integer('nam_conus_id');
            $table->integer('nws_id');
            $table->integer('sst_id');
            $table->integer('gfs_id');
            $table->integer('tide_loc_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->dropColumn(['buoy_id','nearshore_lat','nearshore_lon','bathymetry','ww3_bull_name','nam_hires_id','nam_conus_id','nws_id','sst_id','gfs_id','tide_loc_id']);
        });
    }
}
