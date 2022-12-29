<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWorkForcesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('work_forces', function (Blueprint $table)
		{
			$table->increments('id');
			$table->integer('project_id')->unsigned();
			$table->integer('wbs_id')->unsigned()->nullable();
			$table->text('description');
			$table->string('provider',250);
			$table->text('work_force');
			$table->decimal('total_workers',20,2);
			$table->decimal('man_hours_per_day',20,2);
			$table->date('date');
			$table->integer('user_id')->unsigned();
			$table->foreign('project_id')->references('idproyect')->on('projects');
			$table->foreign('wbs_id')->references('id')->on('cat_code_w_bs');
			$table->foreign('user_id')->references('id')->on('users');
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('work_forces');
	}
}
