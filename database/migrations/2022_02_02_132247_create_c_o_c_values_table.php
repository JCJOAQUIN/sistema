<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCOCValuesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('c_o_c_values', function (Blueprint $table)
		{
			$table->increments('id');
			$table->integer('idUpload')->unsigned()->nullable();
			$table->decimal('costodirectodelaobra',24,6)->nullable();
			$table->decimal('importetotaldelamanodeobragravable',24,6)->nullable();
			$table->decimal('importetotaldelaobra',24,6)->nullable();
			$table->decimal('factorparalaobtenciondelasfp',24,6)->nullable();
			$table->decimal('porcentajedeutilidadbrutapropuesta',24,6)->nullable();
			$table->decimal('tasadeinteresusada',24,6)->nullable();
			$table->decimal('puntosdelbanco',24,6)->nullable();
			$table->text('indicadoreconomicodereferencia')->nullable();
			$table->decimal('isr',24,6)->nullable();
			$table->decimal('ptu',24,6)->nullable();
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
		Schema::dropIfExists('c_o_c_values');
	}
}
