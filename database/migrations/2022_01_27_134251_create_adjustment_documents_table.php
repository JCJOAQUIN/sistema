<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdjustmentDocumentsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('adjustment_documents', function (Blueprint $table)
		{
			$table->increments('iddocumentsAdjustment');
			$table->text('path')->nullable();
			$table->timestamp('date')->nullable();
			$table->integer('idadjustment')->unsigned()->nullable();
			$table->timestamp('updated_at')->nullable();
			$table->foreign('idadjustment')->references('idadjustment')->on('adjustments');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('adjustment_documents');
	}
}
