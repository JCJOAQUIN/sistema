<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConciliationMovementBillsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('conciliation_movement_bills', function (Blueprint $table)
		{
			$table->increments('id');
			$table->integer('idbill')->unsigned()->nullable();
			$table->integer('idmovement')->unsigned()->nullable();
			$table->date('conciliationDate')->nullable();
			$table->tinyInteger('type')->nullable();
			$table->foreign('idbill')->references('idbill')->on('bills');
			$table->foreign('idmovement')->references('idmovement')->on('movements');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('conciliation_movement_bills');
	}
}
