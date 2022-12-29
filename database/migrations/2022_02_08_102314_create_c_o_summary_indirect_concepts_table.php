<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCOSummaryIndirectConceptsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('c_o_summary_indirect_concepts', function (Blueprint $table)
		{
			$table->increments('id');
			$table->integer('idUpload')->unsigned()->nullable();
			$table->integer('type')->nullable();
			$table->text('concepto')->nullable();
			$table->decimal('monto1',24,6)->nullable();
			$table->decimal('porcentaje1',24,6)->nullable();
			$table->decimal('monto2',24,6)->nullable();
			$table->decimal('porcentaje2',24,6)->nullable();
			$table->decimal('montototal',24,6)->nullable();
			$table->decimal('porcentajetotal',24,6)->nullable();
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
		Schema::dropIfExists('c_o_summary_indirect_concepts');
	}
}
