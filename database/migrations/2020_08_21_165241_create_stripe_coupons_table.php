<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStripeCouponsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stripe_coupons', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('coupon_id',30);
            $table->smallInteger('amount_off')->nullable();
            $table->timestamp('stripe_created');
            $table->string('currency',3)->nullable();
            $table->string('duration',9);
            $table->smallInteger('duration_in_months')->nullable();
            $table->tinyInteger('live_mode');
            $table->smallInteger('max_redemptions')->nullable();
            $table->string('name',40);
            $table->float('percent_off')->nullable();
            $table->timestamp('redeem_by')->nullable();
            $table->smallInteger('times_redeeemed');
            $table->tinyInteger('valid');
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
        Schema::dropIfExists('stripe_coupons');
    }
}
