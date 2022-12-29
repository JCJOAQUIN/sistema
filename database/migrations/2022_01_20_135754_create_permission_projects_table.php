<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePermissionProjectsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('permission_projects', function (Blueprint $table)
		{
			$table->increments('idpermission_project');
			$table->integer('user_has_module_iduser_has_module')->unsigned();
			$table->integer('project_id')->unsigned();
			$table->foreign('user_has_module_iduser_has_module')->references('iduser_has_module')->on('user_has_modules');
			$table->foreign('project_id')->references('idproyect')->on('projects');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('permission_projects');
	}
}
