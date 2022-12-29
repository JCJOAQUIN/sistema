<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRetentionPurchasesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('retention_purchases', function (Blueprint $table)
		{
			$table->increments('idretentionPurchase');
			$table->text('name')->nullable();
			$table->decimal('amount',20,2);
			$table->integer('idDetailPurchase')->unsigned()->nullable();
			$table->foreign('idDetailPurchase')->references('idDetailPurchase')->on('detail_purchases');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('retention_purchases');
	}
}
