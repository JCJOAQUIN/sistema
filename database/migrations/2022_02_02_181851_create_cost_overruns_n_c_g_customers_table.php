<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCostOverrunsNCGCustomersTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('cost_overruns_n_c_g_customers', function (Blueprint $table)
		{
			$table->increments('id');
			$table->integer('idUpload')->unsigned()->nullable();
			$table->text('nombrecliente')->nullable();
			$table->text('area')->nullable();
			$table->text('departamento')->nullable();
			$table->text('direccioncliente')->nullable();
			$table->string('coloniacliente',100)->nullable();
			$table->text('codigopostalcliente')->nullable();
			$table->string('ciudadcliente',200)->nullable();
			$table->text('telefonocliente')->nullable();
			$table->string('emailcliente',200)->nullable();
			$table->text('contactocliente')->nullable();
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
		Schema::dropIfExists('cost_overruns_n_c_g_customers');
	}
}
