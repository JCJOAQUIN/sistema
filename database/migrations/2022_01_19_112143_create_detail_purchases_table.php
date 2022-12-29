<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDetailPurchasesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('detail_purchases', function (Blueprint $table)
		{
			$table->increments('idDetailPurchase');
			$table->integer('idPurchase')->unsigned();
			$table->decimal('quantity',20,4)->nullable();
			$table->mediumText('unit')->nullable();
			$table->text('description')->nullable();
			$table->decimal('unitPrice',16,2)->nullable();
			$table->decimal('tax',16,2)->nullable();
			$table->decimal('discount',16,2)->nullable();
			$table->decimal('amount',16,2)->nullable();
			$table->string('typeTax')->nullable();
			$table->decimal('subtotal',16,2)->nullable();
			$table->integer('category')->unsigned()->nullable();
			$table->tinyInteger('statusWarehouse')->default(0);
			$table->mediumText('commentaries')->nullable();
			$table->mediumText('code')->nullable();
			$table->mediumText('measurement')->nullable();
			$table->foreign('idPurchase')->references('idPurchase')->on('purchases');
			$table->foreign('category')->references('id')->on('cat_warehouse_types');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('detail_purchases');
	}
}
