<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRoleHasModuleTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('role_has_module', function (Blueprint $table)
		{
			$table->increments('idrole_has_module');
			$table->integer('role_id')->unsigned();
			$table->integer('module_id')->unsigned();
			$table->foreign('role_id')->references('id')->on('roles');
			$table->foreign('module_id')->references('id')->on('modules');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('role_has_module');
	}
}
