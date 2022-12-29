<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBillPaymentsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('bill_payments', function (Blueprint $table)
		{
			$table->increments('id');
			$table->integer('idBill')->unsigned();
			$table->date('paymentDate')->nullable();
			$table->string('currency',5);
			$table->decimal('exchange',16,2)->nullable();
			$table->string('paymentWay',5)->nullable();
			$table->decimal('amount',16,2)->nullable();
			$table->foreign('idBill')->references('idBill')->on('bills');
			$table->foreign('paymentWay')->references('paymentWay')->on('cat_payment_ways');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('bill_payments');
	}
}
