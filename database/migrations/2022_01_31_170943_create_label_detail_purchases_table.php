<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLabelDetailPurchasesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('label_detail_purchases', function (Blueprint $table)
		{
			$table->increments('idlabelDetailPurchase');
			$table->integer('idlabels')->unsigned();
			$table->integer('idDetailPurchase')->unsigned();
			$table->foreign('idlabels')->references('idlabels')->on('labels');
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
		Schema::dropIfExists('label_detail_purchases');
	}
}
