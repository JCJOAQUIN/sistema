<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateObraProgramUploadsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('obra_program_uploads', function (Blueprint $table)
		{
			$table->increments('id');
			$table->integer('idproyect')->unsigned()->nullable();
			$table->text('file');
			$table->integer('idCreate')->unsigned();
			$table->tinyInteger('status');
			$table->text('name');
			$table->text('client')->nullable();
			$table->text('contestNo')->nullable();
			$table->text('obra')->nullable();
			$table->text('place')->nullable();
			$table->text('city')->nullable();
			$table->date('startObra')->nullable();
			$table->date('endObra')->nullable();
			$table->text('date_type');
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
		Schema::dropIfExists('obra_program_uploads');
	}
}
