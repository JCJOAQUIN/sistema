<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWorkOrderDetailsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('work_order_details', function (Blueprint $table)
		{
			$table->increments('id');
			$table->integer('idWorkOrder')->unsigned();
			$table->text('part')->nullable();
			$table->decimal('quantity',20,2)->nullable();
			$table->text('unit')->nullable();
			$table->text('description')->nullable();
			$table->foreign('idWorkOrder')->references('id')->on('work_orders');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('work_order_details');
	}
}
