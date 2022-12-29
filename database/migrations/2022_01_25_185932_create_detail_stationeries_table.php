<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDetailStationeriesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('detail_stationeries', function (Blueprint $table)
		{
			$table->increments('idStatDetail');
			$table->integer('quantity')->nullable();
			$table->text('product')->nullable();
			$table->text('short_code')->nullable();
			$table->text('long_code')->nullable();
			$table->text('commentaries')->nullable();
			$table->integer('idStat')->unsigned();
			$table->integer('idwarehouse')->unsigned()->nullable();
			$table->decimal('subtotal',20,2)->nullable();
			$table->decimal('iva',20,2)->nullable();
			$table->decimal('total',20,2)->nullable();
			$table->integer('idDetailPurchase')->unsigned()->nullable();
			$table->tinyInteger('category')->default(1);
			$table->date('deliveryDate')->nullable();
			$table->text('measurement')->nullable();
			$table->foreign('idStat')->references('idStationery')->on('stationeries');
			$table->foreign('idwarehouse')->references('idwarehouse')->on('warehouses');
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
		Schema::dropIfExists('detail_stationeries');
	}
}
