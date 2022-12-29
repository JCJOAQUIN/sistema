<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePurchaseEnterprisesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('purchase_enterprises', function (Blueprint $table)
		{
			$table->increments('idpurchaseEnterprise');
			$table->text('title')->nullable();
			$table->date('datetitle')->nullable();
			$table->string('numberOrder',45)->nullable();
			$table->string('reference',100)->nullable();
			$table->text('notes')->nullable();
			$table->date('paymentDate')->nullable();
			$table->string('typeCurrency',45)->nullable();
			$table->decimal('subtotales', 20, 2)->nullable();
			$table->decimal('tax', 20, 2)->nullable();
			$table->decimal('amount', 20, 2)->nullable();
			$table->integer('idFolio')->unsigned()->nullable();
			$table->integer('idKind')->unsigned()->nullable();
			$table->integer('idEnterpriseOrigin')->unsigned()->nullable();
			$table->integer('idDepartamentOrigin')->unsigned()->nullable();
			$table->integer('idAreaOrigin')->unsigned()->nullable();
			$table->integer('idProjectOrigin')->unsigned()->nullable();
			$table->integer('idAccAccOrigin')->unsigned()->nullable();
			$table->integer('idEnterpriseDestiny')->unsigned()->nullable();
			$table->integer('idAccAccDestiny')->unsigned()->nullable();
			$table->integer('idProjectDestiny')->unsigned()->nullable();
			$table->integer('idpaymentMethod')->unsigned()->nullable();
			$table->integer('idEnterpriseOriginR')->unsigned()->nullable();
			$table->integer('idAreaOriginR')->unsigned()->nullable();
			$table->integer('idDepartamentOriginR')->unsigned()->nullable();
			$table->integer('idAccAccOriginR')->unsigned()->nullable();
			$table->integer('idProjectOriginR')->unsigned()->nullable();
			$table->integer('idEnterpriseDestinyR')->unsigned()->nullable();
			$table->integer('idAccAccDestinyR')->unsigned()->nullable();
			$table->integer('idProjectDestinyR')->unsigned()->nullable();
			$table->integer('idbanksAccounts')->unsigned()->nullable();
			$table->foreign('idFolio')->references('folio')->on('request_models');
			$table->foreign('idKind')->references('idrequestkind')->on('request_kinds');
			$table->foreign('idEnterpriseOrigin')->references('id')->on('enterprises');
			$table->foreign('idDepartamentOrigin')->references('id')->on('departments');
			$table->foreign('idAreaOrigin')->references('id')->on('areas');
			$table->foreign('idProjectOrigin')->references('idproyect')->on('projects');
			$table->foreign('idAccAccOrigin')->references('idAccAcc')->on('accounts');
			$table->foreign('idEnterpriseDestiny')->references('id')->on('enterprises');
			$table->foreign('idAccAccDestiny')->references('idAccAcc')->on('accounts');
			$table->foreign('idProjectDestiny')->references('idproyect')->on('projects');
			$table->foreign('idpaymentMethod')->references('idpaymentMethod')->on('payment_methods');
			$table->foreign('idEnterpriseOriginR')->references('id')->on('enterprises');
			$table->foreign('idAreaOriginR')->references('id')->on('areas');
			$table->foreign('idDepartamentOriginR')->references('id')->on('departments');
			$table->foreign('idAccAccOriginR')->references('idAccAcc')->on('accounts');
			$table->foreign('idProjectOriginR')->references('idproyect')->on('projects');
			$table->foreign('idEnterpriseDestinyR')->references('id')->on('enterprises');
			$table->foreign('idAccAccDestinyR')->references('idAccAcc')->on('accounts');
			$table->foreign('idProjectDestinyR')->references('idproyect')->on('projects');
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
		Schema::dropIfExists('purchase_enterprises');
	}
}
