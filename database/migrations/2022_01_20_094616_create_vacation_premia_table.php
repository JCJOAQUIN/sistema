<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVacationPremiaTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('vacation_premia', function (Blueprint $table)
		{
			$table->increments('idvacationPremium');
			$table->decimal('sd',20,6)->nullable();
			$table->decimal('sdi',20,6)->nullable();
			$table->date('dateOfAdmission')->nullable();
			$table->integer('workedDays')->nullable();
			$table->decimal('holidaysDays',20,6)->nullable();
			$table->decimal('bonusDays',20,6)->nullable();
			$table->decimal('holidays',20,6)->nullable();
			$table->decimal('exemptHolidayPremium',20,6)->nullable();
			$table->decimal('holidayPremiumTaxed',20,6)->nullable();
			$table->decimal('subsidy',20,6)->nullable();
			$table->decimal('totalPerceptions',20,6)->nullable();
			$table->decimal('isr',20,6)->nullable();
			$table->decimal('totalTaxes',20,6)->nullable();
			$table->decimal('netIncome',20,6)->nullable();
			$table->integer('idnominaEmployee')->unsigned()->nullable();
			$table->integer('idpaymentMethod')->unsigned()->nullable();
			$table->integer('idemployeeAccounts')->unsigned()->nullable();
			$table->decimal('totalIncomeVP',20,6)->nullable();
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
		Schema::dropIfExists('vacation_premia');
	}
}
