<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStripePromoCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stripe_promo_codes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('promo_id',30);
            $table->tinyInteger('active');
            $table->string('code',40);
            $table->timestamp('stripe_created');
            $table->string('customer',50)->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->tinyInteger('live_mode');
            $table->smallInteger('max_redemptions')->nullable();
            $table->smallInteger('times_redeemed');
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
        Schema::dropIfExists('stripe_promo_codes');
    }
}
