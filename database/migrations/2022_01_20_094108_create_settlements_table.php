<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSettlementsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('settlements', function (Blueprint $table)
		{
			$table->increments('idSettlement');
			$table->decimal('sd',20,6)->nullable();
			$table->decimal('sdi',20,6)->nullable();
			$table->date('admissionDate')->nullable();
			$table->date('downDate')->nullable();
			$table->integer('fullYears')->nullable();
			$table->integer('workedDays')->nullable();
			$table->decimal('holidayDays',20,6)->nullable();
			$table->decimal('bonusDays',20,6)->nullable();
			$table->decimal('seniorityPremium',20,6)->nullable();
			$table->decimal('exemptCompensation',20,6)->nullable();
			$table->decimal('taxedCompensation',20,6)->nullable();
			$table->decimal('holidays',20,6)->nullable();
			$table->decimal('exemptBonus',20,6)->nullable();
			$table->decimal('taxableBonus',20,6)->nullable();
			$table->decimal('holidayPremiumExempt',20,6)->nullable();
			$table->decimal('holidayPremiumTaxed',20,6)->nullable();
			$table->decimal('otherPerception',20,6)->nullable();
			$table->decimal('totalPerceptions',20,6)->nullable();
			$table->decimal('isr',20,6)->nullable();
			$table->decimal('totalRetentions',20,6)->nullable();
			$table->decimal('netIncome',20,6)->nullable();
			$table->integer('idnominaEmployee')->unsigned()->nullable();
			$table->integer('idpaymentMethod')->unsigned()->nullable();
			$table->integer('idemployeeAccounts')->unsigned()->nullable();
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
		Schema::dropIfExists('settlements');
	}
}
