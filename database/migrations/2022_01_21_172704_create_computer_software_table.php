<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateComputerSoftwareTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('computer_software', function (Blueprint $table)
		{
			$table->integer('idComputer')->unsigned();
			$table->integer('idSoftware')->unsigned();
			$table->foreign('idComputer')->references('idComputer')->on('computers');
			$table->foreign('idSoftware')->references('idSoftware')->on('software');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('computer_software');
	}
}
