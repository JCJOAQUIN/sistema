<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('projects', function (Blueprint $table)
		{
			$table->increments('idproyect');
			$table->string('proyectNumber',1000)->nullable();
			$table->text('proyectName',1000)->nullable();
			$table->string('projectCode',200)->nullable();
			$table->string('description',300)->nullable();
			$table->string('place',200)->nullable();
			$table->tinyInteger('kindOfProyect')->nullable();
			$table->text('obra')->nullable();
			$table->text('placeObra')->nullable();
			$table->string('city')->nullable();
			$table->date('startObra')->nullable();
			$table->date('endObra')->nullable();
			$table->text('client')->nullable();
			$table->text('contestNo')->nullable();
			$table->tinyInteger('status')->default(1);
			$table->integer('requisition')->default(0);
			$table->integer('father')->unsigned()->nullable();
			$table->integer('type')->default(1);
			$table->foreign('father')->references('idproyect')->on('projects');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('projects');
	}
}
