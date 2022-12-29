<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStationeriesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('stationeries', function (Blueprint $table)
		{
			$table->increments('idStationery');
			$table->integer('idFolio')->unsigned();
			$table->integer('idKind')->unsigned();
			$table->string('delivery',45)->nullable();
			$table->text('title')->nullable();
			$table->date('datetitle')->nullable();
			$table->decimal('subtotal',20,2)->nullable();
			$table->decimal('iva',20,2)->nullable();
			$table->decimal('total',20,2)->nullable();
			$table->text('subcontractorProvider')->nullable();
			$table->foreign('idFolio')->references('folio')->on('request_models');
			$table->foreign('idKind')->references('idrequestkind')->on('request_kinds');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('stationeries');
	}
}
