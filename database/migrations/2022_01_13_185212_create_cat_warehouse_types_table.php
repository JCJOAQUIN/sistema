<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCatWarehouseTypesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('cat_warehouse_types', function (Blueprint $table)
		{
			$table->increments('id');
			$table->string('description');
			$table->tinyInteger('status')->default(1);
			$table->integer('requisition_types_id')->unsigned();
			$table->foreign('requisition_types_id')->references('id')->on('requisition_types');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('cat_warehouse_types');
	}
}
