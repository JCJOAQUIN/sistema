<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsToPrenominasTable extends Migration
{
	public function up()
	{
		Schema::table('prenominas', function(Blueprint $table) 
		{
			$table->integer('project_id')->unsigned()->nullable();
			$table->integer('user_id')->unsigned()->nullable();

			$table->foreign('project_id')->references('idproyect')->on('projects');
			$table->foreign('user_id')->references('id')->on('users');
		});
	}

	public function down()
	{
		Schema::table('prenominas', function(Blueprint $table) 
		{
			$table->dropForeign(['project_id']);
			$table->dropForeign(['user_id']);
			
			$table->dropColumn('project_id');
			$table->dropColumn('user_id');
		});
	}
}
