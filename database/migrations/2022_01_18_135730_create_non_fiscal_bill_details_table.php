<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNonFiscalBillDetailsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('non_fiscal_bill_details', function (Blueprint $table)
		{
			$table->increments('idBillDetail');
			$table->integer('quantity');
			$table->text('description');
			$table->decimal('value',12,2);
			$table->decimal('amount',12,2);
			$table->decimal('discount',12,2);
			$table->integer('idBill')->unsigned();
			$table->foreign('idBill')->references('idBill')->on('non_fiscal_bills');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('non_fiscal_bill_details');
	}
}
