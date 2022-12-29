<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRefundRetentionsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('refund_retentions', function (Blueprint $table)
		{
			$table->increments('idRefundRetentions');
			$table->text('name')->nullable();
			$table->decimal('amount',20,2);
			$table->integer('idRefundDetail')->unsigned();
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
		Schema::dropIfExists('refund_retentions');
	}
}
