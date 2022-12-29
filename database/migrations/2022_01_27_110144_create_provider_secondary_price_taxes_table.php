<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProviderSecondaryPriceTaxesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('provider_secondary_price_taxes', function (Blueprint $table)
		{
			$table->increments('id');
			$table->string('name',500)->nullable();
			$table->decimal('amount',20,2)->nullable();
			$table->tinyInteger('type')->nullable();
			$table->integer('providerSecondaryPrice_id')->unsigned()->nullable();
			$table->foreign('providerSecondaryPrice_id')->references('id')->on('provider_secondary_prices');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('provider_secondary_price_taxes');
	}
}
