<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddGlobalPermissionToUserHasModulesTable extends Migration
{
	public function up()
	{
		Schema::table('user_has_modules', function(Blueprint $table) 
		{
			$table->tinyInteger('global_permission')->nullable();
		});
	}

	public function down()
	{
		Schema::table('user_has_modules', function(Blueprint $table) 
		{
			$table->dropColumn('global_permission');
		});
	}
}
