<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddGlabalPermissionColumnToModulesTable extends Migration
{
	public function up()
	{
		Schema::table('modules', function(Blueprint $table) 
		{
			$table->tinyInteger('global_permission')->default(0);
		});
	}
	
	public function down()
	{
		Schema::table('modules', function(Blueprint $table) 
		{
			$table->dropColumn('global_permission');
		});
	}
}
