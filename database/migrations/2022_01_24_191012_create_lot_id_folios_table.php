<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLotIdFoliosTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('lot_id_folios', function (Blueprint $table)
		{
			$table->increments('id');
			$table->integer('idLot')->unsigned();
			$table->integer('idComputer')->unsigned()->nullable();
			$table->integer('idFolio')->unsigned();
			$table->foreign('idLot')->references('idlot')->on('lots');
			$table->foreign('idComputer')->references('idComputer')->on('computers');
			$table->foreign('idFolio')->references('folio')->on('request_models');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('lot_id_folios');
	}
}
