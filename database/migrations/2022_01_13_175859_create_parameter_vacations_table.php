<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateParameterVacationsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('parameter_vacations', function (Blueprint $table)
		{
			$table->increments('id');
			$table->string('text',50);
			$table->integer('fromYear');
			$table->integer('toYear');
			$table->integer('days');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('parameter_vacations');
	}
}
