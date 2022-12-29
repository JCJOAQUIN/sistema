<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFlightLodgingTaxesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('flight_lodging_taxes', function (Blueprint $table)
		{
			$table->increments('id');
			$table->string('name',500);
			$table->decimal('amount',20,2);
			$table->tinyInteger('type')->comment('1. impuesto 2. retencion');
			$table->integer('flight_lodging_details_id')->unsigned();
			$table->foreign('flight_lodging_details_id')->references('id')->on('flight_lodging_details');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('flight_lodging_taxes');
	}
}
