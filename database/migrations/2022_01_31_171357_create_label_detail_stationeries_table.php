<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLabelDetailStationeriesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('label_detail_stationeries', function (Blueprint $table)
		{
			$table->increments('idlabelDetailStationery');
			$table->integer('idlabels')->unsigned();
			$table->integer('idStatDetail')->unsigned();
			$table->foreign('idlabels')->references('idlabels')->on('labels');
			$table->foreign('idStatDetail')->references('idStatDetail')->on('detail_stationeries');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('label_detail_stationeries');
	}
}
