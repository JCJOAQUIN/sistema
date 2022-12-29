<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePrenominaEmployeeTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('prenomina_employee', function (Blueprint $table)
		{
			$table->integer('idprenomina')->unsigned();
			$table->integer('idreal_employee')->unsigned();
			$table->foreign('idprenomina')->references('idprenomina')->on('prenominas');
			$table->foreign('idreal_employee')->references('id')->on('real_employees');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('prenomina_employee');
	}
}
