<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVehicleOwnersTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('vehicle_owners', function (Blueprint $table)
		{
			$table->increments('id');
			$table->string('name',200);
			$table->string('last_name',200);
			$table->string('scnd_last_name',200)->nullable();
			$table->string('curp',200)->nullable();
			$table->string('rfc',200)->nullable();
			$table->string('imss',200)->nullable();
			$table->string('email',200);
			$table->string('street',200);
			$table->string('number',200);
			$table->string('colony',200);
			$table->string('cp',200);
			$table->string('city',200);
			$table->integer('state_id')->unsigned();
			$table->integer('type')->nullable();
			$table->integer('users_id')->unsigned();
			$table->foreign('state_id')->references('idstate')->on('states');
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
		Schema::dropIfExists('vehicle_owners');
	}
}
