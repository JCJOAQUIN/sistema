<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePermissionEntsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('permission_ents', function (Blueprint $table)
		{
			$table->increments('idpermission_enterprise');
			$table->integer('user_has_module_iduser_has_module')->unsigned();
			$table->integer('enterprise_id')->unsigned();
			$table->foreign('user_has_module_iduser_has_module')->references('iduser_has_module')->on('user_has_modules');
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
		Schema::dropIfExists('permission_ents');
	}
}
