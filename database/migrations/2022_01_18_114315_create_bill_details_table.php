<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBillDetailsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('bill_details', function (Blueprint $table)
		{
			$table->increments('idBillDetail');
			$table->string('keyProdServ',50);
			$table->string('keyUnit',50);
			$table->decimal('quantity',20,6);
			$table->text('description');
			$table->decimal('value',20,6);
			$table->decimal('amount',20,6);
			$table->decimal('discount',20,6)->nullable();
			$table->string('cat_tax_object_id',5)->nullable();
			$table->integer('idBill')->unsigned();
			$table->foreign('keyProdServ')->references('keyProdServ')->on('cat_prod_servs');
			$table->foreign('keyUnit')->references('keyUnit')->on('cat_unities');
			$table->foreign('idBill')->references('idBill')->on('bills');
			$table->foreign('cat_tax_object_id')->references('id')->on('cat_tax_objects');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('bill_details');
	}
}
