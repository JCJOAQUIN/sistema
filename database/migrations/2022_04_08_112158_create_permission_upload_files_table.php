<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePermissionUploadFilesTable extends Migration
{
	public function up()
	{
		Schema::create('permission_upload_files', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('user_has_module_id')->unsigned();
			$table->integer('permission');
			$table->timestamps();

			$table->foreign('user_has_module_id')->references('iduser_has_module')->on('user_has_modules');
		});
	}

	public function down()
	{
		Schema::dropIfExists('permission_upload_files');
	}
}
