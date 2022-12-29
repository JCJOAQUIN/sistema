<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBillTaxesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('bill_taxes', function (Blueprint $table)
		{
			$table->increments('idBillTaxes');
			$table->decimal('base',20,6)->nullable();
			$table->string('quota',10)->nullable();
			$table->decimal('quotaValue',20,6)->nullable();
			$table->decimal('amount',20,6)->nullable();
			$table->string('tax',5);
			$table->enum('type',['Retención','Traslado']);
			$table->integer('idBillDetail')->unsigned()->nullable();
			$table->integer('related_bill_id')->unsigned()->nullable();
			$table->foreign('tax')->references('tax')->on('cat_taxes');
			$table->foreign('idBillDetail')->references('idBillDetail')->on('bill_details');
			$table->foreign('related_bill_id')->references('id')->on('related_bills');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('bill_taxes');
	}
}
