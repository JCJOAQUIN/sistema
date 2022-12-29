<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCostOverrunsNCGConstructionsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('cost_overruns_n_c_g_constructions', function (Blueprint $table)
		{
			$table->increments('id');
			$table->integer('idUpload')->unsigned()->nullable();
			$table->text('nombredelaobra')->nullable();
			$table->text('direcciondelaobra')->nullable();
			$table->text('coloniadelaobra')->nullable();
			$table->string('ciudaddelaobra',200)->nullable();
			$table->string('estadodelaobra',200)->nullable();
			$table->string('codigopostaldelaobra',100)->nullable();
			$table->text('telefonodelaobra')->nullable();
			$table->string('emaildelaobra',200)->nullable();
			$table->text('responsabledelaobra')->nullable();
			$table->text('cargoresponsabledelaobra')->nullable();
			$table->date('fechainicio')->nullable();
			$table->date('fechaterminacion')->nullable();
			$table->decimal('totalpresupuestoprimeramoneda',24,6)->nullable();
			$table->decimal('totalpresupuestosegundamoneda',24,6)->nullable();
			$table->decimal('porcentajeivapresupuesto',6,3)->nullable();
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
		Schema::dropIfExists('cost_overruns_n_c_g_constructions');
	}
}
