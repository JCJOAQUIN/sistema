<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFlightLodgingsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('flight_lodgings', function (Blueprint $table)
		{
			$table->increments('id');
			$table->text('title')->nullable();
			$table->date('date')->nullable();
			$table->integer('folio_request')->unsigned();
			$table->tinyInteger('pemex_pti')->nullable();
			$table->string('reference',500)->nullable();
			$table->integer('payment_method')->unsigned()->nullable();
			$table->string('currency',500)->nullable();
			$table->string('bill_status',100)->nullable();
			$table->decimal('subtotal',20,2)->nullable();
			$table->decimal('iva',20,2)->nullable();
			$table->decimal('taxes',20,2)->nullable();
			$table->decimal('retentions',20,2)->nullable();
			$table->decimal('total',20,2)->nullable();
			$table->timestamps();
			$table->foreign('folio_request')->references('folio')->on('request_models');
			$table->foreign('payment_method')->references('idpaymentMethod')->on('payment_methods');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('flight_lodgings');
	}
}
