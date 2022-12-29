<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCOCRequiredValuesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('c_o_c_required_values', function (Blueprint $table)
		{
			$table->increments('id');
			$table->integer('idUpload')->unsigned()->nullable();
			$table->decimal('anticipoaproveedoresaliniciodeobra',24,6)->nullable();
			$table->decimal('porcentajedeimpuestosobrenomina',24,6)->nullable();
			$table->integer('presentaciondespuesdelcorte')->nullable();
			$table->integer('revisionyautorizacion')->nullable();
			$table->integer('diasparaelpago')->nullable();
			$table->integer('totaldedias')->nullable();
			$table->integer('periododecobroprimeraestimacion')->nullable();
			$table->integer('periododeentregasegundoanticipo')->nullable();
			$table->integer('redondeoparaprogramadepersonaltecnico')->nullable();
			$table->integer('presentaciondelprogramadepersonaltecnico')->nullable();
			$table->integer('horasjornada')->nullable();
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
		Schema::dropIfExists('c_o_c_required_values');
	}
}
