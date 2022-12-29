<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateControlInternalsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('control_internals', function (Blueprint $table)
		{
			$table->increments('id');
			$table->integer('control_requisitions_id')->unsigned()->nullable();
			$table->integer('control_purchase_orders_id')->unsigned()->nullable();
			$table->integer('control_remittances_id')->unsigned()->nullable();
			$table->integer('control_banks_id')->unsigned()->nullable();
			$table->integer('control_docs_id')->unsigned()->nullable();
			$table->tinyInteger('state')->default(1);
			$table->foreign('control_requisitions_id')->references('id')->on('control_requisitions');
			$table->foreign('control_purchase_orders_id')->references('id')->on('control_purchase_orders');
			$table->foreign('control_remittances_id')->references('id')->on('control_remittances');
			$table->foreign('control_banks_id')->references('id')->on('control_banks');
			$table->foreign('control_docs_id')->references('id')->on('control_docs');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('control_internals');
	}
}
