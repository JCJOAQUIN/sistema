<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompressedFilesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('compressed_files', function (Blueprint $table)
		{
			$table->increments('id');
			$table->text('real_name');
			$table->string('file_name',255);
			$table->string('file_extension',5);
			$table->integer('user_id')->unsigned();
			$table->integer('folder_id')->unsigned()->nullable();
			$table->integer('file_size');
			$table->timestamp('deleted_at')->nullable();
			$table->timestamps();
			$table->foreign('user_id')->references('id')->on('users');
			$table->foreign('folder_id')->references('id')->on('folders');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('compressed_files');
	}
}
