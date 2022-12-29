<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBillNominasTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('bill_nominas', function (Blueprint $table)
		{
			$table->increments('id');
			$table->string('type',3);
			$table->date('paymentDate');
			$table->date('paymentStartDate');
			$table->date('paymentEndDate');
			$table->decimal('paymentDays',13,3);
			$table->decimal('perceptions',16,2);
			$table->decimal('deductions',16,2);
			$table->decimal('other_payments',16,2);
			$table->string('employer_register',100);
			$table->integer('bill_id')->unsigned();
			$table->foreign('bill_id')->references('idBill')->on('bills');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('bill_nominas');
	}
}
