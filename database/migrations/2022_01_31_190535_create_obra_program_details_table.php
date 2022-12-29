<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateObraProgramDetailsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('obra_program_details', function (Blueprint $table)
		{
			$table->increments('id');
			$table->integer('idObraProgramConcept')->unsigned()->nullable();
			$table->decimal('amount',50,25)->nullable();
			$table->integer('type')->nullable();
			$table->integer('order')->nullable();
			$table->foreign('idObraProgramConcept')->references('id')->on('obra_program_concepts');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('obra_program_details');
	}
}
