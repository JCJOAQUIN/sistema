<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePermissionDepsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('permission_deps', function (Blueprint $table)
		{
			$table->increments('idpermission_department');
			$table->integer('user_has_module_iduser_has_module')->unsigned();
			$table->integer('departament_id')->unsigned();
			$table->foreign('user_has_module_iduser_has_module')->references('iduser_has_module')->on('user_has_modules');
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
		Schema::dropIfExists('permission_deps');
	}
}
