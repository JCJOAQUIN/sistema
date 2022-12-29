<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdjustmentFoliosTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('adjustment_folios', function (Blueprint $table)
		{
			$table->increments('idadjustmentFolios');
			$table->integer('idFolio')->unsigned();
			$table->integer('idadjustment')->unsigned();
			$table->foreign('idFolio')->references('folio')->on('request_models');
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
		Schema::dropIfExists('adjustment_folios');
	}
}
