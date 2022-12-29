<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateControlRemittancesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('control_remittances', function (Blueprint $table)
		{
			$table->increments('id');
			$table->text('remittances')->nullable();
			$table->text('data')->nullable();
			$table->text('invoice')->nullable();
			$table->decimal('invoice_amount',20,2)->nullable();
			$table->text('credit_note')->nullable();
			$table->decimal('subtotal',20,2)->nullable();
			$table->decimal('discount',20,2)->nullable();
			$table->decimal('IVA',20,2)->nullable();
			$table->decimal('total',20,2)->nullable();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('control_remittances');
	}
}
