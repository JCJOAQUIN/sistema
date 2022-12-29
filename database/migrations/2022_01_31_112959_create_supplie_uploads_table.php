<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSupplieUploadsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('supplie_uploads', function (Blueprint $table)
		{
			$table->increments('id');
			$table->integer('idproyect')->unsigned()->nullable();
			$table->text('file');
			$table->integer('idCreate')->unsigned();
			$table->tinyInteger('status')->comment('0.- registrando 1.- guardado 3.- finalizado');
			$table->text('name');
			$table->text('client')->nullable();
			$table->text('contestNo')->nullable();
			$table->text('obra')->nullable();
			$table->text('place')->nullable();
			$table->string('city',200)->nullable();
			$table->date('startObra')->nullable();
			$table->date('endObra')->nullable();
			$table->foreign('idproyect')->references('idproyect')->on('projects');
			$table->foreign('idCreate')->references('id')->on('users');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('supplie_uploads');
	}
}
