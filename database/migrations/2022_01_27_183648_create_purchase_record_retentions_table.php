<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePurchaseRecordRetentionsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('purchase_record_retentions', function (Blueprint $table)
		{
			$table->increments('id');
			$table->text('name')->nullable();
			$table->decimal('amount',20,2)->nullable();
			$table->integer('idPurchaseRecordDetail')->unsigned()->nullable();
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
		Schema::dropIfExists('purchase_record_retentions');
	}
}
