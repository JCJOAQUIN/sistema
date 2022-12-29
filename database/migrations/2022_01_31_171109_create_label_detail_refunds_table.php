<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLabelDetailRefundsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('label_detail_refunds', function (Blueprint $table)
		{
			$table->increments('idlabelDetailRefund');
			$table->integer('idlabels')->unsigned();
			$table->integer('idRefundDetail')->unsigned();
			$table->foreign('idlabels')->references('idlabels')->on('labels');
			$table->foreign('idRefundDetail')->references('idRefundDetail')->on('refund_details');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('label_detail_refunds');
	}
}
