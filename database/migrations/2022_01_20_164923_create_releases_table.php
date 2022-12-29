<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReleasesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('releases', function (Blueprint $table)
		{
			$table->increments('idreleases');
			$table->text('title')->nullable();
			$table->text('content')->nullable();
			$table->tinyInteger('visible')->nullable();
			$table->date('date')->nullable();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('releases');
	}
}
