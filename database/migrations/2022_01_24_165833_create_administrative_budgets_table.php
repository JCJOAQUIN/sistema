<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdministrativeBudgetsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('administrative_budgets', function (Blueprint $table)
		{
			$table->increments('id');
			$table->integer('enterprise_id')->unsigned()->nullable();
			$table->integer('department_id')->unsigned()->nullable();
			$table->integer('project_id')->unsigned()->nullable();
			$table->tinyInteger('periodicity')->nullable();
			$table->date('initRange')->nullable();
			$table->date('endRange')->nullable();
			$table->tinyInteger('weekOfYear')->nullable();
			$table->tinyInteger('year')->nullable();
			$table->text('path')->nullable();
			$table->decimal('alert_percent',20,2)->nullable();
			$table->integer('users_id')->unsigned()->nullable();
			$table->timestamps();
			$table->foreign('enterprise_id')->references('id')->on('enterprises');
			$table->foreign('department_id')->references('id')->on('departments');
			$table->foreign('project_id')->references('idproyect')->on('projects');
			$table->foreign('users_id')->references('id')->on('users');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('administrative_budgets');
	}
}
