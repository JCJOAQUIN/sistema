<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCostOverrunsNCGCompetitionsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('cost_overruns_n_c_g_competitions', function (Blueprint $table)
		{
			$table->increments('id');
			$table->integer('idUpload')->unsigned()->nullable();
			$table->date('fechadeconcurso')->nullable();
			$table->string('numerodeconcurso',200)->nullable();
			$table->string('direcciondeconcurso',200)->nullable();
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
		Schema::dropIfExists('cost_overruns_n_c_g_competitions');
	}
}
