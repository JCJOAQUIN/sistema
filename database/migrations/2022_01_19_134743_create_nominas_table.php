<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNominasTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('nominas', function (Blueprint $table)
		{
			$table->increments('idnomina');
			$table->text('title')->nullable();
			$table->date('datetitle')->nullable();
			$table->date('from_date')->nullable();
			$table->date('to_date')->nullable();
			$table->date('down_date')->nullable();
			$table->decimal('amount',16,2)->nullable();
			$table->decimal('ptu_to_pay',16,2)->nullable();
			$table->string('idCatPeriodicity',5)->nullable();
			$table->integer('idFolio')->unsigned()->nullable();
			$table->integer('idKind')->unsigned()->nullable();
			$table->string('idCatTypePayroll',5)->nullable();
			$table->tinyInteger('type_nomina')->comment('1: fiscal, 2: no fiscal, 3: nom35')->nullable();
			$table->decimal('amount_nom35',16,2)->nullable();
			$table->foreign('idCatPeriodicity')->references('c_periodicity')->on('cat_periodicities');
			$table->foreign('idFolio')->references('folio')->on('request_models');
			$table->foreign('idKind')->references('idrequestkind')->on('request_kinds');
			$table->foreign('idCatTypePayroll')->references('id')->on('cat_type_payrolls');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('nominas');
	}
}
