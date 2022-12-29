<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOtherIncomesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('other_incomes', function (Blueprint $table)
		{
			$table->increments('id');
			$table->text('title')->nullable();
			$table->date('datetitle')->nullable();
			$table->integer('idbanksAccounts')->unsigned()->nullable();
			$table->tinyInteger('type_income')->nullable()->comment('1. Préstamo de terceros 2. Reembolso/reintegro 3. Devoluciones 4. Ganancias por inversión');
			$table->decimal('subtotal',20,2)->nullable();
			$table->decimal('total_iva',20,2)->nullable();
			$table->decimal('total_taxes',20,2)->nullable();
			$table->decimal('total_retentions',20,2)->nullable();
			$table->decimal('total',20,2)->nullable();
			$table->integer('idFolio')->unsigned()->nullable();
			$table->integer('idKind')->unsigned()->nullable();
			$table->text('borrower')->nullable();
			$table->string('type_currency',50)->nullable();
			$table->string('pay_mode',50)->nullable();
			$table->string('status_bill',50)->nullable();
			$table->string('reference',50)->nullable();
			$table->foreign('idbanksAccounts')->references('idbanksAccounts')->on('banks_accounts');
			$table->foreign('idFolio')->references('folio')->on('request_models');
			$table->foreign('idKind')->references('idrequestkind')->on('request_kinds');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('other_incomes');
	}
}
