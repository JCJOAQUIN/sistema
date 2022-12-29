<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProviderSecondaryAccountsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('provider_secondary_accounts', function (Blueprint $table)
		{
			$table->increments('id');
			$table->integer('idProviderSecondary')->unsigned()->nullable();
			$table->integer('idBanks')->unsigned()->nullable();
			$table->text('alias')->nullable();
			$table->string('account',45)->nullable();
			$table->string('branch',45)->nullable();
			$table->text('reference')->nullable();
			$table->string('clabe',45)->nullable();
			$table->string('currency',45)->nullable();
			$table->string('agreement',45)->nullable();
			$table->tinyInteger('visible')->default(1);
			$table->string('iban',45)->nullable();
			$table->string('bic_swift',45)->nullable();
			$table->foreign('idProviderSecondary')->references('id')->on('provider_secondaries');
			$table->foreign('idBanks')->references('idBanks')->on('banks');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('provider_secondary_accounts');
	}
}
