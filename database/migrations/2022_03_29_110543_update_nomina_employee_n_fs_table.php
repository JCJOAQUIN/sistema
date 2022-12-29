<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateNominaEmployeeNFsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('nomina_employee_n_fs', function (Blueprint $table)
		{
			$table->dropForeign(['idemployeeAccounts']);
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
		Schema::table('nomina_employee_n_fs', function (Blueprint $table)
		{
			$table->dropForeign(['idemployeeAccounts']);
			$table->foreign('idemployeeAccounts')->references('idpaymentMethod')->on('payment_methods');
		});
	}
}