<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCOConstructionAMIPsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('c_o_construction_a_m_i_ps', function (Blueprint $table)
		{
			$table->increments('id');
			$table->integer('idUpload')->unsigned();
			$table->integer('anticipo1');
			$table->integer('anticipo2');
			$table->integer('monto1');
			$table->integer('monto2');
			$table->integer('importe1');
			$table->integer('importe2');
			$table->integer('periodo');
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
		Schema::dropIfExists('c_o_construction_a_m_i_ps');
	}
}
