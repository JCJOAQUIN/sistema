<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmployeeAttendancesTable extends Migration
{
	public function up()
	{
		Schema::create('employee_attendances', function (Blueprint $table)
		{
			$table->increments('id');
			$table->integer('employee_id')->unsigned();
			$table->decimal('latitude', 10, 8);
			$table->decimal('longitude', 11, 8);
			$table->string('path');
			$table->timestamps();
			$table->foreign('employee_id')->references('id')->on('real_employees');
		});
	}

	public function down()
	{
		Schema::dropIfExists('employee_attendances');
	}
}
