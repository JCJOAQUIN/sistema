<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAreasTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('areas', function (Blueprint $table)
		{
			$table->increments('id');
			$table->string('name')->nullable();
			$table->text('details')->nullable();
			$table->string('responsable',500)->nullable();
			$table->enum('status',['ACTIVE','INACTIVE']);
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('areas');
	}
}
