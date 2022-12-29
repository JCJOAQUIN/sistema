<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLoanEnterprisesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('loan_enterprises', function (Blueprint $table)
		{
			$table->increments('idloanEnterprise');
			$table->string('title',500)->nullable();
			$table->date('datetitle')->nullable();
			$table->tinyInteger('tax')->nullable();
			$table->decimal('amount',20,2)->nullable();
			$table->string('currency',45)->nullable();
			$table->date('paymentDate')->nullable();
			$table->integer('idpaymentMethod')->unsigned()->nullable();
			$table->integer('idEnterpriseOrigin')->unsigned()->nullable();
			$table->integer('idAccAccOrigin')->unsigned()->nullable();
			$table->integer('idEnterpriseDestiny')->unsigned()->nullable();
			$table->integer('idAccAccDestiny')->unsigned()->nullable();
			$table->integer('idFolio')->unsigned()->nullable();
			$table->integer('idKind')->unsigned()->nullable();
			$table->integer('idEnterpriseOriginR')->unsigned()->nullable();
			$table->integer('idAccAccOriginR')->unsigned()->nullable();
			$table->integer('idEnterpriseDestinyR')->unsigned()->nullable();
			$table->integer('idAccAccDestinyR')->unsigned()->nullable();
			$table->foreign('idpaymentMethod')->references('idpaymentMethod')->on('payment_methods');
			$table->foreign('idEnterpriseOrigin')->references('id')->on('enterprises');
			$table->foreign('idAccAccOrigin')->references('idAccAcc')->on('accounts');
			$table->foreign('idEnterpriseDestiny')->references('id')->on('enterprises');
			$table->foreign('idAccAccDestiny')->references('idAccAcc')->on('accounts');
			$table->foreign('idFolio')->references('folio')->on('request_models');
			$table->foreign('idKind')->references('idrequestkind')->on('request_kinds');
			$table->foreign('idEnterpriseOriginR')->references('id')->on('enterprises');
			$table->foreign('idAccAccOriginR')->references('idAccAcc')->on('accounts');
			$table->foreign('idEnterpriseDestinyR')->references('id')->on('enterprises');
			$table->foreign('idAccAccDestinyR')->references('idAccAcc')->on('accounts');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('loan_enterprises');
	}
}
