<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdjustmentRetentionsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('adjustment_retentions', function (Blueprint $table)
		{
			$table->increments('idretentionAdjustment');
			$table->text('name')->nullable();
			$table->decimal('amount',20,2);
			$table->integer('idadjustmentDetail')->unsigned()->nullable();
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
		Schema::dropIfExists('adjustment_retentions');
	}
}
