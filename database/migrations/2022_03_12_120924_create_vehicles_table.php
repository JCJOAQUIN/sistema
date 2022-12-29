<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVehiclesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('vehicles', function (Blueprint $table)
		{
			$table->increments('id');
			$table->string('brand',500)->nullable();
			$table->string('sub_brand',500)->nullable();
			$table->string('model',250)->nullable();
			$table->string('serial_number',500)->nullable();
			$table->string('plates',250)->nullable();
			$table->string('kilometer',250)->nullable();
			$table->string('vehicle_status',100)->nullable();
			$table->integer('enterprise_id')->unsigned()->nullable();
			$table->integer('vehicles_owners_id')->unsigned()->nullable();
			$table->integer('real_employee_id')->unsigned()->nullable();
			$table->string('fuel_type',200)->nullable();
			$table->string('tag',200)->nullable();
			$table->date('date_verification')->nullable();
			$table->string('company',250)->nullable();
			$table->date('expiration_date')->nullable();
			$table->string('owner_type',50)->nullable();
			$table->string('owner_external',50)->nullable();
			$table->integer('users_id')->unsigned()->nullable();
			$table->foreign('enterprise_id')->references('id')->on('enterprises');
			$table->foreign('vehicles_owners_id')->references('id')->on('vehicle_owners');
			$table->foreign('real_employee_id')->references('id')->on('real_employees');
			$table->foreign('users_id')->references('id')->on('users');
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('vehicles');
	}
}
