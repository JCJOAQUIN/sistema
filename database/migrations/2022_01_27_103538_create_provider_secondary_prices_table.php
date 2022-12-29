<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProviderSecondaryPricesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('provider_secondary_prices', function (Blueprint $table)
		{
			$table->increments('id');
			$table->decimal('unitPrice',20,2)->nullable();
			$table->decimal('subtotal',20,2)->nullable();
			$table->text('typeTax')->nullable();
			$table->decimal('iva',20,2)->nullable();
			$table->decimal('taxes',20,2)->nullable();
			$table->decimal('retentions',20,2)->nullable();
			$table->decimal('total',20,2)->nullable();
			$table->integer('idRequisitionDetail')->unsigned()->nullable();
			$table->integer('idRequisitionHasProvider')->unsigned()->nullable();
			$table->integer('user_id')->unsigned()->nullable();
			$table->foreign('idRequisitionDetail')->references('id')->on('requisition_details');
			$table->foreign('idRequisitionHasProvider')->references('id')->on('requisition_has_providers');
			$table->foreign('user_id')->references('id')->on('users');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('provider_secondary_prices');
	}
}
