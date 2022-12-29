<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateComputerEmailsAccountsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('computer_emails_accounts', function (Blueprint $table)
		{
			$table->increments('idcomputerEmailsAccounts');
			$table->string('email_account',500);
			$table->string('alias_account',500);
			$table->integer('idComputer')->unsigned()->nullable();
			$table->foreign('idComputer')->references('idComputer')->on('computers');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('computer_emails_accounts');
	}
}
