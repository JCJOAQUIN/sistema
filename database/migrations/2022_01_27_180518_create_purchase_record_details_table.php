<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePurchaseRecordDetailsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('purchase_record_details', function (Blueprint $table)
		{
			$table->increments('id');
			$table->integer('idPurchaseRecord')->unsigned();
			$table->decimal('quantity', 24, 6)->nullable();
			$table->text('unit')->nullable();
			$table->text('description')->nullable();
			$table->decimal('unitPrice',20,2)->nullable();
			$table->decimal('tax',20,2)->nullable();
			$table->decimal('discount',20,2)->nullable();
			$table->decimal('subtotal',20,2)->nullable();
			$table->decimal('total',20,2)->nullable();
			$table->string('typeTax',50)->nullable();
			$table->foreign('idPurchaseRecord')->references('id')->on('purchase_records');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('purchase_record_details');
	}
}
