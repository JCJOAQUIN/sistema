<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTaxesPurchasesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('taxes_purchases', function (Blueprint $table)
		{
			$table->increments('idtaxesPurchase');
			$table->text('name')->nullable();
			$table->decimal('amount',20,2);
			$table->integer('idDetailPurchase')->unsigned();
			$table->foreign('idDetailPurchase')->references('idDetailPurchase')->on('detail_purchases');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('taxes_purchases');
	}
}
