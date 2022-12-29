<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmployeeFaceEnrollmentsTable extends Migration
{
	public function up()
	{
		Schema::create('employee_face_enrollments', function (Blueprint $table)
		{
			$table->increments('id');
			$table->integer('employee_id')->unsigned();
			$table->string('audit_trail_image_path')->nullable();
			$table->string('low_quality_audit_trail_image_path')->nullable();
			$table->string('face_scan_path')->nullable();
			$table->string('external_database_ref_id');
			$table->timestamps();
			$table->foreign('employee_id')->references('id')->on('real_employees');
		});
	}

	public function down()
	{
		Schema::dropIfExists('employee_face_enrollments');
	}
}
