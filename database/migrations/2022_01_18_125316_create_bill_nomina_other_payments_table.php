<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBillNominaOtherPaymentsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('bill_nomina_other_payments', function (Blueprint $table)
		{
			$table->increments('id');
			$table->string('type',5);
			$table->string('otherPaymentKey',5);
			$table->text('concept');
			$table->decimal('amount',16,2);
			$table->integer('bill_nomina_id')->unsigned();
			$table->decimal('subsidy_caused',16,2);
			$table->foreign('type')->references('id')->on('cat_other_payments');
			$table->foreign('bill_nomina_id')->references('id')->on('bill_nominas');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('bill_nomina_other_payments');
	}
}
