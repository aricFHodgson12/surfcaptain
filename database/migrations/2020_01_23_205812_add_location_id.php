<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLocationId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('location_forecast_params', function (Blueprint $table) {

            if (! Schema::hasColumn('location_forecast_params', 'location_id')) {
                $table->integer('location_id');
            }

            $table->boolean('nws_data');
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
            $table->dropColumn('location_id');
            $table->dropColumn('nws_data');
        });
    }
}
