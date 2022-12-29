<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIncomesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('incomes', function (Blueprint $table)
		{
			$table->increments('idIncome');
			$table->integer('idClient')->unsigned()->nullable();
			$table->integer('idFolio')->unsigned();
			$table->integer('idKind')->unsigned();
			$table->text('title')->nullable();
			$table->date('datetitle')->nullable();
			$table->text('reference')->nullable();
			$table->text('notes')->nullable();
			$table->decimal('discount', 20, 2)->nullable();
			$table->string('badge',400)->nullable();
			$table->tinyInteger('actspend')->nullable();
			$table->string('paymentMode',45)->nullable();
			$table->string('typeCurrency',45)->nullable();
			$table->text('path')->nullable();
			$table->string('billStatus',45)->nullable();
			$table->string('exitGroup',45)->nullable();
			$table->decimal('subtotales', 20, 2)->nullable();
			$table->decimal('tax', 20, 2)->nullable();
			$table->decimal('amount', 20, 2)->nullable();
			$table->integer('idbanksAccounts')->unsigned()->nullable();
			$table->foreign('idClient')->references('idClient')->on('clients');
			$table->foreign('idFolio')->references('folio')->on('request_models');
			$table->foreign('idKind')->references('idrequestkind')->on('request_kinds');
			$table->foreign('idbanksAccounts')->references('idbanksAccounts')->on('banks_accounts');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('incomes');
	}
}
