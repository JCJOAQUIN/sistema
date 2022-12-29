<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSalariesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('salaries', function (Blueprint $table)
		{
			$table->increments('idSalary');
			$table->decimal('sd',20,6)->nullable();
			$table->decimal('sdi',20,6)->nullable();
			$table->integer('workedDays')->nullable();
			$table->decimal('daysForImss',16,2)->nullable();
			$table->decimal('salary',20,6)->nullable();
			$table->decimal('loan_perception',20,6)->nullable();
			$table->decimal('puntuality',20,6)->nullable();
			$table->decimal('assistance',20,6)->nullable();
			$table->integer('extra_hours')->nullable();
			$table->decimal('extra_time',20,6)->nullable();
			$table->decimal('extra_time_taxed',20,6)->nullable();
			$table->integer('holidays')->nullable();
			$table->decimal('holiday',20,6)->nullable();
			$table->decimal('holiday_taxed',20,6)->nullable();
			$table->integer('sundays')->nullable();
			$table->decimal('exempt_sunday',20,6)->nullable();
			$table->decimal('taxed_sunday',20,6)->nullable();
			$table->decimal('subsidy',20,6)->nullable();
			$table->decimal('totalPerceptions',20,6)->nullable();
			$table->decimal('imss',20,6)->nullable();
			$table->decimal('infonavit',20,6)->nullable();
			$table->decimal('infonavitComplement',20,6)->nullable();
			$table->decimal('fonacot',20,6)->nullable();
			$table->decimal('loan_retention',20,6)->nullable();
			$table->decimal('isrRetentions',20,6)->nullable();
			$table->decimal('alimony',20,6)->nullable();
			$table->decimal('totalRetentions',20,6)->nullable();
			$table->decimal('netIncome',20,6)->nullable();
			$table->decimal('subsidyCaused',20,6)->nullable();
			$table->integer('idnominaEmployee')->unsigned()->nullable();
			$table->integer('idpaymentMethod')->unsigned()->nullable();
			$table->integer('idemployeeAccounts')->unsigned()->nullable();
			$table->integer('idAccountBeneficiary')->nullable();
			$table->decimal('other_retention_amount',20,6)->nullable();
			$table->text('other_retention_concept')->nullable();
			$table->decimal('risk_number',20,6)->nullable();
			$table->decimal('uma',20,6)->nullable();
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
		Schema::dropIfExists('salaries');
	}
}
