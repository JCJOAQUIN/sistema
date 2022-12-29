<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePurchaseTempsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('purchase_temps', function (Blueprint $table)
		{
			$table->increments('id');
			$table->integer('idProvider')->unsigned()->nullable();
			$table->integer('idKind')->unsigned()->nullable();
			$table->text('title')->nullable();
			$table->date('datetitle')->nullable();
			$table->mediumText('reference')->nullable();
			$table->mediumText('notes')->nullable();
			$table->decimal('discount',16,2)->nullable();
			$table->string('paymentMode',500)->nullable();
			$table->string('typeCurrency',500)->nullable();
			$table->mediumText('path')->nullable();
			$table->mediumText('billStatus')->nullable();
			$table->string('exitGroup',500)->nullable();
			$table->decimal('subtotal',16,2)->nullable();
			$table->decimal('tax',16,2)->nullable();
			$table->decimal('amount',16,2)->nullable();
			$table->integer('provider_has_banks_id')->unsigned()->nullable();
			$table->string('numberOrder',500)->nullable();
			$table->date('payment_date')->nullable();
			$table->integer('idAutomaticRequests')->unsigned()->nullable();
			$table->foreign('idProvider')->references('idProvider')->on('providers');
			$table->foreign('idKind')->references('idrequestkind')->on('request_kinds');
			$table->foreign('provider_has_banks_id')->references('id')->on('provider_banks');
			$table->foreign('idAutomaticRequests')->references('id')->on('automatic_requests');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('purchase_temps');
	}
}
