<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdjustmentDetailsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('adjustment_details', function (Blueprint $table)
		{
			$table->increments('idadjustmentDetail');
			$table->integer('quantity')->nullable();
			$table->string('unit',45)->nullable();
			$table->text('description')->nullable();
			$table->decimal('unitPrice',20,2)->nullable();
			$table->decimal('tax',20,2)->nullable();
			$table->string('typeTax')->nullable();
			$table->decimal('subtotal',20,2)->nullable();
			$table->decimal('amount',20,2)->nullable();
			$table->integer('idadjustment')->unsigned()->nullable();
			$table->foreign('idadjustment')->references('idadjustment')->on('adjustments');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('adjustment_details');
	}
}
