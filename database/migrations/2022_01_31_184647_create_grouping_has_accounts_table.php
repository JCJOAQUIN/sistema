<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGroupingHasAccountsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('grouping_has_accounts', function (Blueprint $table)
		{
			$table->increments('id');
			$table->integer('idGroupingAccount')->unsigned()->nullable();
			$table->integer('idEnterprise')->unsigned()->nullable();
			$table->integer('idAccAcc')->unsigned()->nullable();
			$table->foreign('idGroupingAccount')->references('id')->on('grouping_accounts');
			$table->foreign('idEnterprise')->references('id')->on('enterprises');
			$table->foreign('idAccAcc')->references('idAccAcc')->on('accounts');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('grouping_has_accounts');
	}
}
