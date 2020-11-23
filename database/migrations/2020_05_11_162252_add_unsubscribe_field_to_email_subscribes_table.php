<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUnsubscribeFieldToEmailSubscribesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('email_subscribes', function (Blueprint $table) {
            $table->timestamp('unsubscribe_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('email_subscribes', function (Blueprint $table) {
            $table->dropColumn('unsubscribe_date');
        });
    }
}
