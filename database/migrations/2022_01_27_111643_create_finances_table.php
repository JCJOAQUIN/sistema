<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFinancesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('finances', function (Blueprint $table)
		{
			$table->increments('id');
			$table->integer('idFolio')->unsigned();
			$table->integer('idKind')->unsigned();
			$table->text('title')->nullable();
			$table->date('datetitle')->nullable();
			$table->string('kind', 10)->nullable();
			$table->string('paymentMethod',100)->nullable();
			$table->integer('bank')->unsigned()->nullable();
			$table->integer('account')->unsigned()->nullable();
			$table->integer('card')->unsigned()->nullable();
			$table->string('currency',10)->nullable();
			$table->decimal('subtotal',20,2)->nullable();
			$table->decimal('tax',20,2)->nullable();
			$table->string('taxType', 5)->nullable();
			$table->decimal('amount',20,2)->nullable();
			$table->text('note')->nullable();
			$table->tinyInteger('week')->nullable();
			$table->foreign('idFolio')->references('folio')->on('request_models');
			$table->foreign('idKind')->references('idrequestkind')->on('request_kinds');
			$table->foreign('bank')->references('idBanks')->on('banks');
			$table->foreign('account')->references('id')->on('bank_accounts');
			$table->foreign('card')->references('idcreditCard')->on('credit_cards');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('finances');
	}
}
