<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCOCostPeriodProgramsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('c_o_cost_period_programs', function (Blueprint $table)
		{
			$table->increments('id');
			$table->integer('idUpload')->unsigned()->nullable();
			$table->integer('idProgramado')->nullable();
			$table->decimal('totalcostodirecto',24,6)->nullable();
			$table->decimal('costomateriales',24,6)->nullable();
			$table->decimal('costomanodeobra',24,6)->nullable();
			$table->decimal('costoequipo',24,6)->nullable();
			$table->decimal('costootrosinsumos',24,6)->nullable();
			$table->foreign('idUpload')->references('id')->on('cost_overruns');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('c_o_cost_period_programs');
	}
}
