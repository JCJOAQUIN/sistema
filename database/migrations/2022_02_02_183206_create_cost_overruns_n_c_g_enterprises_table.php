<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCostOverrunsNCGEnterprisesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('cost_overruns_n_c_g_enterprises', function (Blueprint $table)
		{
			$table->increments('id');
			$table->integer('idUpload')->unsigned()->nullable();
			$table->text('razonsocial')->nullable();
			$table->text('domicilio')->nullable();
			$table->text('colonia')->nullable();
			$table->string('ciudad',200)->nullable();
			$table->string('estado',200)->nullable();
			$table->text('rfc')->nullable();
			$table->text('telefono')->nullable();
			$table->string('email1',200)->nullable();
			$table->string('email2',200)->nullable();
			$table->string('email3',200)->nullable();
			$table->text('cmic')->nullable();
			$table->text('infonavit')->nullable();
			$table->text('imss')->nullable();
			$table->string('responsable',200)->nullable();
			$table->string('cargo',200)->nullable();
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
		Schema::dropIfExists('cost_overruns_n_c_g_enterprises');
	}
}
