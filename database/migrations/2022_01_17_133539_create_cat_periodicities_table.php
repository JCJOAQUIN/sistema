<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCatPeriodicitiesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('cat_periodicities', function (Blueprint $table)
		{
			$table->string('c_periodicity',5)->primary();
			$table->string('description',500);
			$table->integer('days')->nullable();
			$table->date('validity_start')->nullable();
			$table->date('validity_end')->nullable();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('cat_periodicities');
	}
}
