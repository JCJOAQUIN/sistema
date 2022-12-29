<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProfitSharingsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('profit_sharings', function (Blueprint $table)
		{
			$table->increments('idprofitSharing');
			$table->decimal('sd',20,6)->nullable();
			$table->decimal('sdi',20,6)->nullable();
			$table->integer('workedDays')->nullable();
			$table->decimal('totalSalary',20,6)->nullable();
			$table->decimal('ptuForDays',20,6)->nullable();
			$table->decimal('ptuForSalary',20,6)->nullable();
			$table->decimal('totalPtu',20,6)->nullable();
			$table->decimal('exemptPtu',20,6)->nullable();
			$table->decimal('taxedPtu',20,6)->nullable();
			$table->decimal('subsidy',20,6)->nullable();
			$table->decimal('totalPerceptions',20,6)->nullable();
			$table->decimal('isrRetentions',20,6)->nullable();
			$table->decimal('totalRetentions',20,6)->nullable();
			$table->decimal('netIncome',20,6)->nullable();
			$table->integer('idnominaEmployee')->unsigned()->nullable();
			$table->integer('idpaymentMethod')->unsigned()->nullable();
			$table->integer('idemployeeAccounts')->unsigned()->nullable();
			$table->decimal('totalIncomePS',20,6)->nullable();
			$table->decimal('alimony',20,6)->nullable();
			$table->integer('idAccountBeneficiary')->nullable();
			$table->foreign('idnominaEmployee')->references('idnominaEmployee')->on('nomina_employees');
			$table->foreign('idpaymentMethod')->references('idpaymentMethod')->on('payment_methods');
			$table->foreign('idemployeeAccounts')->references('id')->on('employee_accounts');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('profit_sharings');
	}
}
