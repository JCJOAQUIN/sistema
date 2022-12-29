<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePurchaseEnterpriseDetailsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('purchase_enterprise_details', function (Blueprint $table)
		{
			$table->increments('idPurchaseEnterpriseDetail');
			$table->decimal('quantity',20,2)->nullable();
			$table->text('unit')->nullable();
			$table->text('description')->nullable();
			$table->decimal('unitPrice',20,2)->nullable();
			$table->decimal('tax',20,2)->nullable();
			$table->decimal('discount',20,2)->nullable();
			$table->decimal('amount',20,2)->nullable();
			$table->string('typeTax', 100)->nullable();
			$table->decimal('subtotal', 20,2)->nullable();
			$table->integer('idpurchaseEnterprise')->unsigned()->nullable();
			$table->foreign('idpurchaseEnterprise')->references('idpurchaseEnterprise')->on('purchase_enterprises');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('purchase_enterprise_details');
	}
}
