<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCatMeasurementTypesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('cat_measurement_types', function (Blueprint $table)
		{
			$table->increments('id');
			$table->string('description',500)->nullable();
			$table->string('abbreviation',10)->nullable();
			$table->string('type',500)->nullable();
			$table->decimal('equivalence',24,10)->nullable();
			$table->integer('father')->unsigned()->nullable();
			$table->integer('child_order')->nullable();
			$table->foreign('father')->references('id')->on('cat_measurement_types');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('cat_measurement_types');
	}
}
