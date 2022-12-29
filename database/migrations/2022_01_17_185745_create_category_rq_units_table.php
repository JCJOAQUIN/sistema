<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCategoryRqUnitsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('category_rq_units', function (Blueprint $table)
		{
			$table->integer('unit_id')->unsigned();
			$table->integer('rq_id')->unsigned();
			$table->integer('category_id')->unsigned()->nullable();
			$table->foreign('unit_id')->references('id')->on('units');
			$table->foreign('rq_id')->references('id')->on('requisition_types');
			$table->foreign('category_id')->references('id')->on('cat_warehouse_types');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('category_rq_units');
	}
}
