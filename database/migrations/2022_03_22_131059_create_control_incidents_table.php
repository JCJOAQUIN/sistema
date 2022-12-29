<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateControlIncidentsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('control_incidents', function (Blueprint $table)
		{
			$table->increments('id');
			$table->integer('incident_number');
			$table->integer('project_id')->unsigned();
			$table->integer('wbs_id')->unsigned()->nullable();
			$table->integer('real_employee_id')->unsigned();
			$table->date('date_incident');
			$table->integer('impact_level');
			$table->integer('status');
			$table->string('description',300);
			$table->string('causes',300);
			$table->string('recommendation',300);
			$table->string('communique',300);
			$table->integer('user_id')->unsigned();
			$table->foreign('project_id')->references('idproyect')->on('projects');
			$table->foreign('wbs_id')->references('id')->on('cat_code_w_bs');
			$table->foreign('real_employee_id')->references('id')->on('real_employees');
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
		Schema::dropIfExists('control_incidents');
	}
}
