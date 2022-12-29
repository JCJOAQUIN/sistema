<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJobPositionsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('job_positions', function (Blueprint $table)
		{
			$table->increments('id');
			$table->string('name',500)->nullable();
			$table->text('description')->nullable();
			$table->integer('immediate_boss')->unsigned()->nullable();
			$table->integer('user_id')->unsigned()->nullable();
			$table->timestamps();
			$table->foreign('immediate_boss')->references('id')->on('job_positions');
			$table->foreign('user_id')->references('id')->on('users');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('job_positions');
	}
}
