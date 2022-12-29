<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePurchaseRecordsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('purchase_records', function (Blueprint $table)
		{
			$table->increments('id');
			$table->integer('idFolio')->unsigned();
			$table->integer('idKind')->unsigned();
			$table->text('title')->nullable();
			$table->date('datetitle')->nullable();
			$table->text('reference')->nullable();
			$table->text('notes')->nullable();
			$table->text('paymentMethod')->nullable();
			$table->string('typeCurrency',100)->nullable();
			$table->text('billStatus')->nullable();
			$table->decimal('subtotal',20,2)->nullable();
			$table->decimal('tax',20,2)->nullable();
			$table->decimal('amount_taxes',20,2)->nullable();
			$table->decimal('amount_retention',20,2)->nullable();
			$table->decimal('total',20,2)->nullable();
			$table->text('numberOrder')->nullable();
			$table->text('provider')->nullable();
			$table->integer('idEnterprisePayment')->unsigned()->nullable();
			$table->integer('idAccAccPayment')->unsigned()->nullable();
			$table->integer('idcreditCard')->unsigned()->nullable();
			$table->foreign('idFolio')->references('folio')->on('request_models');
			$table->foreign('idKind')->references('idrequestkind')->on('request_kinds');
			$table->foreign('idEnterprisePayment')->references('id')->on('enterprises');
			$table->foreign('idAccAccPayment')->references('idAccAcc')->on('accounts');
			$table->foreign('idcreditCard')->references('idcreditCard')->on('credit_cards');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('purchase_records');
	}
}
