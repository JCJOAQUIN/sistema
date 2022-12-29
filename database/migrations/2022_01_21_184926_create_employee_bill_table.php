<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmployeeBillTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('employee_bill', function (Blueprint $table)
		{
			$table->integer('idBill')->unsigned()->nullable();
			$table->integer('idNominaEmployee')->unsigned()->nullable();
			$table->foreign('idBill')->references('idBill')->on('bills');
			$table->foreign('idNominaEmployee')->references('idNominaEmployee')->on('nomina_employees');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('employee_bill');
	}
}
