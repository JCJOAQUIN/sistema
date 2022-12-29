<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUnitPricesDetailsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('unit_prices_details', function (Blueprint $table)
		{
			$table->increments('id');
			$table->integer('idUpload')->unsigned();
			$table->text('code')->nullable();
			$table->text('concept')->nullable();
			$table->string('measurement',200)->nullable();
			$table->decimal('amount',50,25)->nullable();
			$table->decimal('price',50,25)->nullable();
			$table->decimal('import',50,25)->nullable();
			$table->decimal('incidence',50,25)->nullable();
			$table->integer('father')->unsigned()->nullable();
			$table->tinyInteger('type')->nullable()->comment('0.- partida 1.- análisis 2.- análisis título 3.- grupo 4.- concepto 5.- importe 6.- rendimiento 7.- subtotal 8.- costo directo');
			$table->string('op',100)->nullable();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('unit_prices_details');
	}
}
