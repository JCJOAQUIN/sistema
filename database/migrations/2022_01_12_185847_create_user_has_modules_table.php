<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserHasModulesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('user_has_modules', function (Blueprint $table)
		{
			$table->increments('iduser_has_module');
			$table->integer('user_id')->unsigned();
			$table->integer('module_id')->unsigned();
			$table->foreign('user_id')->references('id')->on('users');
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
		Schema::dropIfExists('user_has_modules');
	}
}
