<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWorkerDataPlacesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('worker_data_places', function (Blueprint $table)
		{
			$table->integer('idWorkingData')->unsigned();
			$table->integer('idPlace')->unsigned();
			$table->foreign('idWorkingData')->references('id')->on('worker_datas');
			$table->foreign('idPlace')->references('id')->on('places');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('worker_data_places');
	}
}
