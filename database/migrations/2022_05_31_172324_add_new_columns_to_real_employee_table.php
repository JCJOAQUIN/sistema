<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewColumnsToRealEmployeeTable extends Migration
{
	public function up()
	{
		Schema::table('real_employees', function(Blueprint $table) 
		{
			$table->string('replace',500)->nullable();
			$table->text('purpose')->nullable();
			$table->text('requeriments')->nullable();
			$table->text('observations')->nullable();
			$table->tinyInteger('qualified_employee')->nullable();
		});
	}

	public function down()
	{
		Schema::table('real_employees', function(Blueprint $table) 
		{
			$table->dropColumn('replace');
			$table->dropColumn('purpose');
			$table->dropColumn('requeriments');
			$table->dropColumn('observations');
			$table->dropColumn('qualified_employee');
		});
	}
}
