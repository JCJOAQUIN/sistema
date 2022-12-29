<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCatWarehouseConceptsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('cat_warehouse_concepts', function (Blueprint $table)
		{
			$table->increments('id');
			$table->text('description');
			$table->integer('warehouseType')->unsigned();
			$table->foreign('warehouseType')->references('id')->on('cat_warehouse_types');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('cat_warehouse_concepts');
	}
}
