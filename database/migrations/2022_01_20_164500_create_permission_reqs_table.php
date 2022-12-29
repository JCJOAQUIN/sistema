<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePermissionReqsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('permission_reqs', function (Blueprint $table)
		{
			$table->increments('id');
			$table->integer('user_has_module_id')->unsigned();
			$table->integer('requisition_type_id')->unsigned();
			$table->foreign('user_has_module_id')->references('iduser_has_module')->on('user_has_modules');
			$table->foreign('requisition_type_id')->references('id')->on('requisition_types');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('permission_reqs');
	}
}
