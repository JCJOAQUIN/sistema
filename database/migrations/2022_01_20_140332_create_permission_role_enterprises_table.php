<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePermissionRoleEnterprisesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('permission_role_enterprises', function (Blueprint $table)
		{
			$table->increments('idpermission_role_ent');
			$table->integer('role_has_module_idrole_has_module')->unsigned();
			$table->integer('enterprise_id')->unsigned();
			$table->foreign('role_has_module_idrole_has_module','pre_rhmid_foreign')->references('idrole_has_module')->on('role_has_module');
			$table->foreign('enterprise_id')->references('id')->on('enterprises');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('permission_role_enterprises');
	}
}
