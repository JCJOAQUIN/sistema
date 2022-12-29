<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVersionLotsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('version_lots', function (Blueprint $table)
		{
			$table->increments('id');
			$table->integer('idlot')->unsigned();
			$table->decimal('total', 20, 2)->nullable();
			$table->decimal('subtotal', 20, 2)->nullable();
			$table->decimal('iva', 20, 2)->nullable();
			$table->decimal('articles', 20, 2)->nullable();
			$table->date('date')->nullable();
			$table->integer('idEnterprise')->unsigned();
			$table->integer('idElaborate')->unsigned();
			$table->integer('account')->unsigned()->nullable();
			$table->integer('idFolio')->unsigned()->nullable();
			$table->integer('category')->nullable();
			$table->foreign('idlot')->references('idlot')->on('lots');
			$table->foreign('idEnterprise')->references('id')->on('enterprises');
			$table->foreign('idElaborate')->references('id')->on('users');
			$table->foreign('account')->references('idAccAcc')->on('accounts');
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
		Schema::dropIfExists('version_lots');
	}
}
