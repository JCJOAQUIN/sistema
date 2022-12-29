<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnPaymentIdToPartialPayments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('partial_payments', function(Blueprint $table)
        {
            $table->integer('payment_id')->unsigned()->nullable();
            $table->foreign('payment_id')->references('idpayment')->on('payments');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('partial_payments', function(Blueprint $table)
        {
			$table->dropForeign(['payment_id']);
            $table->dropColumn('payment_id');
        });
    }
}
