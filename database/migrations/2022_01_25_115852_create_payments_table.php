<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaymentsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('payments', function (Blueprint $table)
		{
			$table->increments('idpayment');
			$table->integer('fiscal')->nullable();
			$table->decimal('subtotal',20,2)->nullable();
			$table->decimal('iva',20,2)->nullable();
			$table->decimal('tax',20,2)->nullable();
			$table->decimal('retention',20,2)->nullable();
			$table->decimal('amount',20,2)->nullable();
			$table->decimal('subtotal_real',20,2)->nullable();
			$table->decimal('iva_real',20,2)->nullable();
			$table->decimal('tax_real',20,2)->nullable();
			$table->decimal('retention_real',20,2)->nullable();
			$table->decimal('amount_real',20,2)->nullable();
			$table->integer('account')->unsigned();
			$table->dateTime('paymentDate')->nullable();
			$table->dateTime('elaborateDate')->nullable();
			$table->integer('idFolio')->unsigned();
			$table->integer('idKind')->unsigned();
			$table->integer('idEnterprise')->unsigned()->nullable();
			$table->integer('idRequest')->unsigned();
			$table->text('commentaries')->nullable();
			$table->tinyInteger('statusConciliation')->default(0);
			$table->integer('idnominaEmployee')->unsigned()->nullable();
			$table->text('exchange_rate')->nullable();
			$table->text('exchange_rate_description')->nullable();
			$table->integer('idmovement')->unsigned()->nullable();
			$table->dateTime('conciliationDate')->nullable();
			$table->tinyInteger('type')->default(1)->comment('1. pago nmal 2. pago pension');
			$table->text('beneficiary')->nullable();
			$table->integer('remittance')->default(0);
			$table->integer('partial_id')->unsigned()->nullable();
			$table->foreign('account')->references('idAccAcc')->on('accounts');
			$table->foreign('idFolio')->references('folio')->on('request_models');
			$table->foreign('idKind')->references('idrequestkind')->on('request_kinds');
			$table->foreign('idEnterprise')->references('id')->on('enterprises');
			$table->foreign('idRequest')->references('id')->on('users');
			$table->foreign('idnominaEmployee')->references('idnominaEmployee')->on('nomina_employees');
			$table->foreign('partial_id')->references('id')->on('partial_payments');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('payments');
	}
}
