<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProcessFoldersTable extends Migration
{
	public function up()
	{
		Schema::create('process_folders', function (Blueprint $table) 
		{
			$table->increments('id');
			$table->text('text');
			$table->integer('parent')->unsigned()->nullable();
			$table->integer('user_id')->unsigned();
			$table->timestamps();
			$table->foreign('parent')->references('id')->on('folders');
			$table->foreign('user_id')->references('id')->on('users');
		});
	}

	public function down()
	{
		Schema::dropIfExists('process_folders');
	}
}
