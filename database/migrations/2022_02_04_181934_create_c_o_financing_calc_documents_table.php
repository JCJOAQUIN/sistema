<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCOFinancingCalcDocumentsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('c_o_financing_calc_documents', function (Blueprint $table)
		{
			$table->increments('id');
			$table->integer('idUpload')->unsigned()->nullable();
			$table->integer('idDocEmpresa')->nullable();
			$table->tinyInteger('importetotal')->nullable();
			$table->tinyInteger('costodirectoindirecto')->nullable();
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
		Schema::dropIfExists('c_o_financing_calc_documents');
	}
}
