<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCOConstructionBudgetExceedsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('c_o_construction_budget_exceeds', function (Blueprint $table)
		{
			$table->increments('id');
			$table->integer('idUpload')->unsigned()->nullable();
			$table->integer('numero')->nullable();
			$table->text('anticipos')->nullable();
			$table->decimal('porcentaje',24,6)->nullable();
			$table->decimal('importeaejercer',24,6)->nullable();
			$table->decimal('importedeanticipo',24,6)->nullable();
			$table->text('periododeentrega')->nullable();
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
		Schema::dropIfExists('c_o_construction_budget_exceeds');
	}
}
