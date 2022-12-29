<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCostOverrunsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('cost_overruns', function (Blueprint $table)
		{
			$table->increments('id');
			$table->integer('idproyect')->unsigned();
			$table->text('file');
			$table->integer('idCreate')->unsigned();
			$table->tinyInteger('status');
			$table->text('name');
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
		Schema::dropIfExists('cost_overruns');
	}
}
