<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBankAccountsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('bank_accounts', function (Blueprint $table)
		{
			$table->increments('id');
			$table->text('alias')->nullable();
			$table->integer('id_enterprise')->unsigned();
			$table->integer('id_accounting_account')->unsigned();
			$table->integer('id_bank')->unsigned();
			$table->string('currency',5);
			$table->string('clabe',20)->nullable();
			$table->string('account',20)->nullable();
			$table->text('kind');
			$table->text('description');
			$table->tinyInteger('status')->default(1);
			$table->timestamps();
			$table->foreign('id_enterprise')->references('id')->on('enterprises');
			$table->foreign('id_accounting_account')->references('idAccAcc')->on('accounts');
			$table->foreign('id_bank')->references('idBanks')->on('banks');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('bank_accounts');
	}
}
