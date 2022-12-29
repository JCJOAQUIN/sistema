<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateModulesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('modules', function (Blueprint $table)
		{
			$table->increments('id');
			$table->string('name');
			$table->integer('father')->unsigned()->nullable();
			$table->string('category',600)->nullable();
			$table->string('details');
			$table->string('icon')->nullable();
			$table->string('url');
			$table->tinyInteger('permissionRequire')->default(1);
			$table->integer('itemOrder')->default(0);
			$table->tinyInteger('hybrid')->default(0);
			$table->tinyInteger('active')->default(1);
			$table->timestamps();
			$table->foreign('father')->references('id')->on('modules');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('modules');
	}
}
