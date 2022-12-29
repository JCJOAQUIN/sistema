<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRefundsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('refunds', function (Blueprint $table)
		{
			$table->increments('idRefund');
			$table->integer('idFolio')->unsigned();
			$table->integer('idKind')->unsigned();
			$table->text('title')->nullable();
			$table->date('datetitle')->nullable();
			$table->decimal('total',20,2)->nullable();
			$table->integer('idEmployee')->unsigned()->nullable();
			$table->integer('idUsers')->unsigned()->nullable();
			$table->text('reference')->nullable();
			$table->string('currency',100)->nullable();
			$table->integer('idpaymentMethod')->unsigned()->nullable();
			$table->integer('idRequisition')->unsigned()->nullable();
			$table->foreign('idFolio')->references('folio')->on('request_models');
			$table->foreign('idKind')->references('idrequestkind')->on('request_kinds');
			$table->foreign('idEmployee')->references('idEmployee')->on('employees');
			$table->foreign('idUsers')->references('id')->on('users');
			$table->foreign('idpaymentMethod')->references('idpaymentMethod')->on('payment_methods');
			$table->foreign('idRequisition')->references('folio')->on('request_models');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('refunds');
	}
}
