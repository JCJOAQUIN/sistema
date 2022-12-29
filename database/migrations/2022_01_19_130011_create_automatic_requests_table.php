<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAutomaticRequestsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('automatic_requests', function (Blueprint $table)
		{
			$table->increments('id');
			$table->integer('kind')->unsigned();
			$table->tinyInteger('taxPayment')->default(0)->nullable();
			$table->integer('idAccAcc')->unsigned()->nullable();
			$table->integer('idEnterprise')->unsigned()->nullable();
			$table->integer('idDepartment')->unsigned()->nullable();
			$table->integer('idArea')->unsigned()->nullable();
			$table->integer('idProject')->unsigned()->nullable();
			$table->integer('idRequest')->unsigned()->nullable();
			$table->integer('idElaborate')->unsigned()->nullable();
			$table->string('periodicity')->nullable();
			$table->integer('day_monthlyOn')->nullable();
			$table->integer('day_twiceMonthly_one')->nullable();
			$table->integer('day_twiceMonthly_two')->nullable();
			$table->integer('day_weeklyOn')->nullable();
			$table->string('day_yearly')->nullable();
			$table->tinyInteger('status')->nullable();
			$table->timestamps();
			$table->foreign('idAccAcc')->references('idAccAcc')->on('accounts');
			$table->foreign('idEnterprise')->references('id')->on('enterprises');
			$table->foreign('idDepartment')->references('id')->on('departments');
			$table->foreign('idArea')->references('id')->on('areas');
			$table->foreign('idProject')->references('idproyect')->on('projects');
			$table->foreign('idRequest')->references('id')->on('users');
			$table->foreign('idElaborate')->references('id')->on('users');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('automatic_requests');
	}
}
