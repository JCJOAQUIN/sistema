<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSoftwareTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('software', function (Blueprint $table)
		{
			$table->increments('idsoftware');
			$table->string('name',45);
			$table->tinyInteger('kind');
			$table->tinyInteger('required');
			$table->decimal('cost',16,2)->nullable();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('software');
	}
}
