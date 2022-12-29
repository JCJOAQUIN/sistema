<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRequisitionDetailsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('requisition_details', function (Blueprint $table)
		{
			$table->increments('id');
			$table->integer('idRequisition')->unsigned();
			$table->integer('category')->unsigned()->nullable();
			$table->text('part')->nullable();
			$table->decimal('quantity',20,2)->nullable();
			$table->text('unit')->nullable();
			$table->text('name')->nullable();
			$table->text('description')->nullable();
			$table->decimal('exists_warehouse',20,2)->nullable();
			$table->integer('idRequisitionHasProvider')->unsigned()->nullable();
			$table->text('code')->nullable();
			$table->string('type_currency',50)->nullable();
			$table->text('measurement')->nullable();
			$table->string('period',500)->nullable();
			$table->text('brand')->nullable();
			$table->text('model')->nullable();
			$table->string('usage_time',500)->nullable();
			$table->integer('cat_procurement_material_id')->unsigned()->nullable();
			$table->foreign('idRequisition')->references('id')->on('requisitions');
			$table->foreign('category')->references('id')->on('cat_warehouse_types');
			$table->foreign('idRequisitionHasProvider')->references('id')->on('requisition_has_providers');
			$table->foreign('cat_procurement_material_id')->references('id')->on('cat_procurement_materials');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('requisition_details');
	}
}
