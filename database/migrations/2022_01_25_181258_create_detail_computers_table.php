<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDetailComputersTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('detail_computers', function (Blueprint $table)
		{
			$table->increments('idDetComputer');
			$table->integer('idComputer')->unsigned();
			$table->integer('idDevices')->unsigned();
			$table->date('assignedDate');
			$table->tinyInteger('configuration')->default(0);
			$table->foreign('idComputer')->references('idComputer')->on('computers');
			$table->foreign('idDevices')->references('iddevices')->on('devices');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('detail_computers');
	}
}
