<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePartialPaymentsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('partial_payments', function (Blueprint $table)
		{
			$table->increments('id');
			$table->decimal('payment',20,2)->nullable();
			$table->tinyInteger('tipe')->default(1);
			$table->date('date_requested')->nullable();
			$table->date('date_delivery')->nullable();
			$table->integer('purchase_id')->unsigned()->nullable();
			$table->foreign('purchase_id')->references('idPurchase')->on('purchases');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('partial_payments');
	}
}
