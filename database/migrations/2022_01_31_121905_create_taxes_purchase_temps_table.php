<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTaxesPurchaseTempsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('taxes_purchase_temps', function (Blueprint $table)
		{
			$table->increments('id');
			$table->text('name')->nullable();
			$table->decimal('amount',20,2);
			$table->integer('idDetailPurchaseTemp')->unsigned();
			$table->foreign('idDetailPurchaseTemp')->references('id')->on('detail_purchase_temps');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('taxes_purchase_temps');
	}
}
