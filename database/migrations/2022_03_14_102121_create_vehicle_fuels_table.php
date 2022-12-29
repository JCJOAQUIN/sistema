<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVehicleFuelsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('vehicle_fuels', function (Blueprint $table)
		{
			$table->increments('id');
			$table->string('fuel_type',200);
			$table->string('tag',200)->nullable();
			$table->date('date');
			$table->decimal('total',20,2);
			$table->integer('vehicles_id')->unsigned();
			$table->integer('users_id')->unsigned();
			$table->timestamps();
			$table->foreign('vehicles_id')->references('id')->on('vehicles');
			$table->foreign('users_id')->references('id')->on('users');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('vehicle_fuels');
	}
}
