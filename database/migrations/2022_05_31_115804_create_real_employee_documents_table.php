<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRealEmployeeDocumentsTable extends Migration
{
	public function up()
	{
		Schema::create('real_employee_documents', function (Blueprint $table) 
		{
			$table->increments('id');
			$table->string('name',200);
			$table->string('path',200);
			$table->integer('real_employee_id');
			$table->timestamps();
		});
	}

	public function down()
	{
		Schema::dropIfExists('real_employee_documents');
	}
}
