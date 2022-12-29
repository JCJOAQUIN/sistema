<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCreditCardsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('credit_cards', function (Blueprint $table)
		{
			$table->increments('idcreditCard');
			$table->integer('idBanks')->unsigned()->nullable();
			$table->text('alias')->nullable();
			$table->text('name_credit_card')->nullable();
			$table->integer('assignment')->unsigned()->nullable();
			$table->text('credit_card')->nullable();
			$table->text('status')->nullable();
			$table->text('type_credit')->nullable();
			$table->text('type_credit_other')->nullable();
			$table->date('cutoff_date')->nullable();
			$table->integer('idEnterprise')->unsigned()->nullable();
			$table->integer('idAccAcc')->unsigned()->nullable();
			$table->date('payment_date')->nullable();
			$table->decimal('limit_credit', 20, 2)->nullable();
			$table->string('type_currency',10)->nullable();
			$table->tinyInteger('principal_aditional')->nullable();
			$table->text('principal_card')->nullable();
			$table->integer('principal_card_id')->unsigned()->nullable();
			$table->foreign('idBanks')->references('idBanks')->on('banks');
			$table->foreign('assignment')->references('id')->on('users');
			$table->foreign('idEnterprise')->references('id')->on('enterprises');
			$table->foreign('idAccAcc')->references('idAccAcc')->on('accounts');
			$table->foreign('principal_card_id')->references('idcreditCard')->on('credit_cards');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('credit_cards');
	}
}
