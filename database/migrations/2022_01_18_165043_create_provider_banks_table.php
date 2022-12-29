<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProviderBanksTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('provider_banks', function (Blueprint $table)
		{
			$table->increments('id');
			$table->integer('provider_idProvider')->unsigned()->nullable();
			$table->integer('provider_data_id')->unsigned()->nullable();
			$table->integer('banks_idBanks')->unsigned()->nullable();
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
			$table->foreign('provider_idProvider')->references('idProvider')->on('providers');
			$table->foreign('provider_data_id')->references('id')->on('provider_datas');
			$table->foreign('banks_idBanks')->references('idBanks')->on('banks');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('provider_banks');
	}
}
