<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVehicleDocumentsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('vehicle_documents', function (Blueprint $table)
		{
			$table->increments('id');
			$table->string('name',200);
			$table->string('path',200);
			$table->date('date')->nullable();
			$table->integer('cat_type_document_id')->unsigned();
			$table->integer('vehicles_mechanical_services_id')->unsigned()->nullable();
			$table->integer('vehicles_fines_id')->unsigned()->nullable();
			$table->integer('vehicles_taxes_id')->unsigned()->nullable();
			$table->integer('vehicles_fuel_id')->unsigned()->nullable();
			$table->integer('vehicles_insurances_id')->unsigned()->nullable();
			$table->integer('vehicles_id')->unsigned()->nullable();
			$table->integer('users_id')->unsigned();
			$table->timestamps();
			$table->foreign('cat_type_document_id')->references('id')->on('cat_type_documents');
			$table->foreign('vehicles_mechanical_services_id')->references('id')->on('vehicle_mechanical_services');
			$table->foreign('vehicles_fines_id')->references('id')->on('vehicle_fines');
			$table->foreign('vehicles_taxes_id')->references('id')->on('vehicle_taxes');
			$table->foreign('vehicles_fuel_id')->references('id')->on('vehicle_fuels');
			$table->foreign('vehicles_insurances_id')->references('id')->on('vehicle_insurances');
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
		Schema::dropIfExists('vehicle_documents');
	}
}
