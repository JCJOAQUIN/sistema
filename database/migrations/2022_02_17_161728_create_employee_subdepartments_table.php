<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmployeeSubdepartmentsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('employee_subdepartments', function (Blueprint $table)
		{
			$table->increments('id');
			$table->integer('working_data_id')->unsigned();
			$table->integer('subdepartment_id')->unsigned();
			$table->timestamps();
			$table->foreign('working_data_id')->references('id')->on('worker_datas');
			$table->foreign('subdepartment_id')->references('id')->on('subdepartments');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('employee_subdepartments');
	}
}
