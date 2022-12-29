<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVehicleMechanicalServicesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('vehicle_mechanical_services', function (Blueprint $table)
		{
			$table->increments('id');
			$table->date('date_last_service');
			$table->date('next_service_date');
			$table->text('repairs');
			$table->decimal('total',20,2);
			$table->integer('vehicles_id')->unsigned()->nullable();
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
		Schema::dropIfExists('vehicle_mechanical_services');
	}
}
