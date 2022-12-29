<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCOPeriodProgramsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('c_o_period_programs', function (Blueprint $table)
		{
			$table->increments('id');
			$table->integer('idUpload')->unsigned()->nuallble();
			$table->integer('programado')->nuallble();
			$table->string('titulo',100)->nuallble();
			$table->integer('diasnaturales')->nuallble();
			$table->integer('diastotales')->nuallble();
			$table->decimal('factorano',24,6)->nuallble();
			$table->integer('ano')->nuallble();
			$table->decimal('importedelperiodo',24,6)->nuallble();
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
		Schema::dropIfExists('c_o_period_programs');
	}
}
