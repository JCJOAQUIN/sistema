<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnStatusPersonalToRequisitionEmployeesTable extends Migration
{
	public function up()
	{
		Schema::table('requisition_employees', function(Blueprint $table) 
		{
			$table->tinyInteger('status_personal')->nullable()->default('0');
		});
	}

	public function down()
	{
		Schema::table('requisition_employees', function(Blueprint $table) 
		{
			$table->dropColumn('status_personal');
		});
	}
}
