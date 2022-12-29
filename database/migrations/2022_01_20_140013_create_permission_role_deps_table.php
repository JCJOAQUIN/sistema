<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePermissionRoleDepsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('permission_role_deps', function (Blueprint $table)
		{
			$table->increments('idpermission_role_dep');
			$table->integer('role_has_module_idrole_has_module')->unsigned();
			$table->integer('departament_id')->unsigned();
			$table->foreign('role_has_module_idrole_has_module')->references('idrole_has_module')->on('role_has_module');
			$table->foreign('departament_id')->references('id')->on('departments');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('permission_role_deps');
	}
}
