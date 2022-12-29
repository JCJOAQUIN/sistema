<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNominaEmployeeNFsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('nomina_employee_n_fs', function (Blueprint $table)
		{
			$table->increments('idnominaemployeenf');
			$table->integer('idnominaEmployee')->unsigned()->nullable();
			$table->integer('idpaymentMethod')->unsigned()->nullable();
			$table->integer('idemployeeAccounts')->unsigned()->nullable();
			$table->text('reference')->nullable();
			$table->decimal('discount',16,2)->nullable();
			$table->text('reasonDiscount')->nullable();
			$table->decimal('amount',16,2)->nullable();
			$table->text('reasonAmount')->nullable();
			$table->decimal('netIncome',16,2)->default(0.00);
			$table->decimal('complementPartial',16,2)->nullable();
			$table->foreign('idnominaEmployee')->references('idnominaEmployee')->on('nomina_employees');
			$table->foreign('idpaymentMethod')->references('idpaymentMethod')->on('payment_methods');
			$table->foreign('idemployeeAccounts')->references('idpaymentMethod')->on('payment_methods');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('nomina_employee_n_fs');
	}
}
