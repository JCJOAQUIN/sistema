<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdjustmentDetailLabelsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('adjustment_detail_labels', function (Blueprint $table)
		{
			$table->increments('idadjustmentDetailLabel');
			$table->integer('idlabels')->unsigned();
			$table->integer('idadjustmentDetail')->unsigned();
			$table->foreign('idlabels')->references('idlabels')->on('labels');
			$table->foreign('idadjustmentDetail')->references('idadjustmentDetail')->on('adjustment_details');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('adjustment_detail_labels');
	}
}
