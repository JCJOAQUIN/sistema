<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFoldersTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('folders', function (Blueprint $table)
		{
			$table->increments('id');
			$table->text('text');
			$table->integer('parent')->unsigned()->nullable();
			$table->integer('user_id')->unsigned();
			$table->timestamp('deleted_at')->nullable();
			$table->timestamps();
			$table->foreign('parent')->references('id')->on('folders');
			$table->foreign('user_id')->references('id')->on('users');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('folders');
	}
}
