<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNonFiscalBillsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('non_fiscal_bills', function (Blueprint $table)
		{
			$table->increments('idBill');
			$table->string('rfc',50);
			$table->text('businessName');
			$table->string('clientRfc',50);
			$table->text('clientBusinessName');
			$table->date('expeditionDate');
			$table->integer('folio')->unsigned();
			$table->text('conditions')->nullable();
			$table->tinyInteger('status')->default(0);
			$table->decimal('subtotal',16,2);
			$table->decimal('discount',16,2);
			$table->decimal('total',16,2);
			$table->string('paymentMethod',5);
			$table->string('paymentWay',5);
			$table->string('currency',5);
			$table->tinyInteger('statusConciliation')->default(0);
			$table->foreign('folio')->references('folio')->on('request_models');
			$table->foreign('paymentMethod')->references('paymentMethod')->on('cat_payment_methods');
			$table->foreign('paymentWay')->references('paymentWay')->on('cat_payment_ways');
			$table->foreign('currency')->references('currency')->on('cat_currencies');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('non_fiscal_bills');
	}
}
