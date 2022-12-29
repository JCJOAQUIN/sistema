<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLoansTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('loans', function (Blueprint $table)
		{
			$table->increments('idLoan');
			$table->string('kindOfAcount',45)->nullable();
			$table->integer('idUsers')->unsigned()->nullable();
			$table->text('title')->nullable();
			$table->date('datetitle')->nullable();
			$table->string('reference',500)->nullable();
			$table->decimal('amount',20,2)->nullable();
			$table->tinyInteger('transfer')->default(0);
			$table->tinyInteger('periodicity')->nullable();
			$table->string('beneficiary',45)->nullable();
			$table->integer('idFolio')->unsigned()->nullable();
			$table->integer('idKind')->unsigned()->nullable();
			$table->integer('idEmployee')->unsigned()->nullable();
			$table->string('path',45)->nullable();
			$table->integer('idpaymentMethod')->unsigned()->nullable();
			$table->foreign('idUsers')->references('id')->on('users');
			$table->foreign('idFolio')->references('folio')->on('request_models');
			$table->foreign('idKind')->references('idrequestkind')->on('request_kinds');
			$table->foreign('idEmployee')->references('idEmployee')->on('employees');
			$table->foreign('idpaymentMethod')->references('idpaymentMethod')->on('payment_methods');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('loans');
	}
}
