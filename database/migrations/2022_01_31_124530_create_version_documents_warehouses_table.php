<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVersionDocumentsWarehousesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('version_documents_warehouses', function (Blueprint $table)
		{
			$table->increments('id');
			$table->integer('iddocumentsWarehouse')->unsigned();
			$table->text('path')->nullable();
			$table->integer('idlot')->unsigned()->nullable();
			$table->integer('version');
			$table->foreign('iddocumentsWarehouse')->references('iddocumentsWarehouse')->on('documents_warehouses');
			$table->foreign('idlot')->references('idlot')->on('lots');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('version_documents_warehouses');
	}
}
