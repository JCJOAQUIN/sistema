<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVideoTutorialsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('video_tutorials', function (Blueprint $table) 
		{
			$table->increments('id');
			$table->string('name');
			$table->string('url');
			$table->integer('module_id')->unsigned();
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
		Schema::dropIfExists('video_tutorials');
	}
}
