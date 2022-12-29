<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCOInterestsToConsiderDocumentsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('c_o_interests_to_consider_documents', function (Blueprint $table)
		{
			$table->increments('id');
			$table->integer('idUpload')->unsigned()->nullable();
			$table->integer('idDocEmpresa')->nullable();
			$table->tinyInteger('negativos')->nullable();
			$table->tinyInteger('ambos')->nullable();
			$table->tinyInteger('tasaactiva')->nullable();
			$table->tinyInteger('tasapasiva')->nullable();
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
		Schema::dropIfExists('c_o_interests_to_consider_documents');
	}
}
