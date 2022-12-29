<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePurchaseRecordLabelsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('purchase_record_labels', function (Blueprint $table)
		{
			$table->increments('id');
			$table->integer('idLabel')->unsigned();
			$table->integer('idPurchaseRecordDetail')->unsigned();
			$table->foreign('idLabel')->references('idlabels')->on('labels');
			$table->foreign('idPurchaseRecordDetail')->references('id')->on('purchase_record_details');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('purchase_record_labels');
	}
}
