<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCOGeneralFinancialsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('c_o_general_financials', function (Blueprint $table)
		{
			$table->increments('id');
			$table->integer('idUpload')->unsigned()->nuallble();
			$table->decimal('indicadoreconomicodereferencia',24,6)->nuallble();
			$table->decimal('puntosdeintermediaciondelabanca',24,6)->nuallble();
			$table->decimal('tasadeinteresdiaria',24,6)->nuallble();
			$table->integer('diasparapagodeestimaciones')->nuallble();
			$table->decimal('aplicablealperiodo',24,6)->nuallble();
			$table->decimal('porcentajedefinancieamiento',24,6)->nuallble();
			$table->foreign('idUpload')->references('id')->on('cost_overruns');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('c_o_general_financials');
	}
}
