<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVersionWarehousesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('version_warehouses', function (Blueprint $table)
		{
			$table->increments('id');
			$table->integer('concept')->unsigned();
			$table->integer('quantity');
			$table->integer('quantityReal');
			$table->text('short_code')->nullable();
			$table->text('long_code')->nullable();
			$table->integer('measurement')->unsigned()->nullable();
			$table->text('commentaries')->nullable();
			$table->decimal('amountUnit',20,2)->nullable();
			$table->string('typeTax',50)->nullable();
			$table->decimal('iva',20,2)->nullable();
			$table->decimal('subtotal',20,2)->nullable();
			$table->decimal('amount',20,2);
			$table->integer('idLot')->unsigned();
			$table->integer('warehouseType')->unsigned()->nullable();
			$table->integer('place_location')->unsigned()->nullable();
			$table->integer('idWarehouse')->unsigned();
			$table->foreign('concept')->references('id')->on('cat_warehouse_concepts');
			$table->foreign('idLot')->references('idlot')->on('lots');
			$table->foreign('warehouseType')->references('id')->on('cat_warehouse_types');
			$table->foreign('place_location')->references('id')->on('places');
			$table->foreign('idWarehouse')->references('idwarehouse')->on('warehouses');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('version_warehouses');
	}
}
