<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCostOverrunsNCGHeadersTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('cost_overruns_n_c_g_headers', function (Blueprint $table)
		{
			$table->increments('id');
			$table->integer('idUpload')->unsigned()->nullable();
			$table->integer('plazocalculado')->nullable();
			$table->integer('plazoreal')->nullable();
			$table->integer('decimalesredondeo')->nullable();
			$table->string('primeramoneda',100)->nullable();
			$table->string('segundamoneda',100)->nullable();
			$table->string('remateprimeramoneda',100)->nullable();
			$table->string('rematesegundamoneda',100)->nullable();
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
		Schema::dropIfExists('cost_overruns_n_c_g_headers');
	}
}
