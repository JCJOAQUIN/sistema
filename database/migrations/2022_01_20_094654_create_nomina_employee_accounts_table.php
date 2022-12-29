<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNominaEmployeeAccountsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('nomina_employee_accounts', function (Blueprint $table)
		{
			$table->increments('id');
			$table->integer('idEmployeeAccounts')->unsigned()->nullable();
			$table->integer('idnominaemployeenf')->unsigned()->nullable();
			$table->integer('idSalary')->unsigned()->nullable();
			$table->integer('idBonus')->unsigned()->nullable();
			$table->integer('idLiquidation')->unsigned()->nullable();
			$table->integer('idSettlement')->unsigned()->nullable();
			$table->integer('idvacationPremium')->unsigned()->nullable();
			$table->integer('idprofitSharing')->unsigned()->nullable();
			$table->foreign('idEmployeeAccounts')->references('id')->on('employee_accounts');
			$table->foreign('idnominaemployeenf')->references('idnominaemployeenf')->on('nomina_employee_n_fs');
			$table->foreign('idSalary')->references('idSalary')->on('salaries');
			$table->foreign('idBonus')->references('idBonus')->on('bonuses');
			$table->foreign('idLiquidation')->references('idLiquidation')->on('liquidations');
			$table->foreign('idSettlement')->references('idSettlement')->on('settlements');
			$table->foreign('idvacationPremium')->references('idvacationPremium')->on('vacation_premia');
			$table->foreign('idprofitSharing')->references('idprofitSharing')->on('profit_sharings');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('nomina_employee_accounts');
	}
}
