<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBonusesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('bonuses', function (Blueprint $table)
		{
			$table->increments('idBonus');
			$table->decimal('sd',20,6)->nullable();
			$table->decimal('sdi',20,6)->nullable();
			$table->date('dateOfAdmission')->nullable();
			$table->integer('daysForBonuses')->nullable();
			$table->decimal('proportionalPartForChristmasBonus',20,6)->nullable();
			$table->decimal('exemptBonus',20,6)->nullable();
			$table->decimal('taxableBonus',20,6)->nullable();
			$table->decimal('totalPerceptions',20,6)->nullable();
			$table->decimal('isr',20,6)->nullable();
			$table->decimal('totalTaxes',20,6)->nullable();
			$table->decimal('netIncome',20,6)->nullable();
			$table->integer('idnominaEmployee')->unsigned()->nullable();
			$table->integer('idpaymentMethod')->unsigned()->nullable();
			$table->integer('idemployeeAccounts')->unsigned()->nullable();
			$table->decimal('totalIncomeBonus',20,6)->nullable();
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
		Schema::dropIfExists('bonuses');
	}
}
