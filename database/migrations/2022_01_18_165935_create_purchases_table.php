<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePurchasesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('purchases', function (Blueprint $table)
		{
			$table->increments('idPurchase');
			$table->integer('idProvider')->unsigned()->nullable();
			$table->integer('provider_data_id')->unsigned()->nullable();
			$table->integer('idFolio')->unsigned()->nullable();
			$table->integer('idKind')->unsigned()->nullable();
			$table->text('title')->nullable();
			$table->date('datetitle')->nullable();
			$table->mediumText('reference')->nullable();
			$table->mediumText('notes')->nullable();
			$table->decimal('discount',16,2)->nullable();
			$table->string('badge',400)->nullable();
			$table->tinyInteger('actspend')->nullable();
			$table->string('paymentMode',500)->nullable();
			$table->string('typeCurrency',500)->nullable();
			$table->mediumText('path')->nullable();
			$table->mediumText('billStatus')->nullable();
			$table->string('exitGroup',500)->nullable();
			$table->decimal('subtotales',16,2)->nullable();
			$table->decimal('tax',16,2)->nullable();
			$table->decimal('amount',16,2)->nullable();
			$table->integer('provider_has_banks_id')->unsigned()->nullable();
			$table->string('numberOrder',500)->nullable();
			$table->integer('idRequisition')->unsigned()->nullable();
			$table->foreign('idProvider')->references('idProvider')->on('providers');
			$table->foreign('provider_data_id')->references('id')->on('provider_datas');
			$table->foreign('idFolio')->references('folio')->on('request_models');
			$table->foreign('idKind')->references('idrequestkind')->on('request_kinds');
			$table->foreign('provider_has_banks_id')->references('id')->on('provider_banks');
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
		Schema::dropIfExists('purchases');
	}
}
