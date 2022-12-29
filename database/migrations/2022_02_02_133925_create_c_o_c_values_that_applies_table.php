<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCOCValuesThatAppliesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('c_o_c_values_that_applies', function (Blueprint $table)
		{
			$table->increments('id');
			$table->integer('idUpload')->unsigned()->nullable();
			$table->integer('tipodeanticipo')->nullable();
			$table->integer('modelodecalculodelfinanciamiento')->nullable();
			$table->integer('interesesaconsiderarenelfinanciamiento')->nullable();
			$table->integer('tasaactiva')->nullable();
			$table->integer('calculodelcargoadicional')->nullable();
			$table->integer('diasaconsiderarenelaño')->nullable();
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
		Schema::dropIfExists('c_o_c_values_that_applies');
	}
}
