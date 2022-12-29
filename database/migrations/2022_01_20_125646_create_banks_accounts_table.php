<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBanksAccountsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('banks_accounts', function (Blueprint $table)
		{
			$table->increments('idbanksAccounts');
			$table->integer('idBanks')->unsigned()->nullable();
			$table->text('alias')->nullable();
			$table->text('account')->nullable();
			$table->text('branch')->nullable();
			$table->text('reference')->nullable();
			$table->text('clabe')->nullable();
			$table->text('currency')->nullable();
			$table->text('agreement')->nullable();
			$table->integer('idEnterprise')->unsigned()->nullable();
			$table->integer('idAccAcc')->unsigned()->nullable();
			$table->foreign('idBanks')->references('idBanks')->on('banks');
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
		Schema::dropIfExists('banks_accounts');
	}
}
