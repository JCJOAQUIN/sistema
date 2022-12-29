<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBoardroomsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('boardrooms', function (Blueprint $table)
		{
			$table->increments('id');
			$table->text('name');
			$table->text('description')->nullable();
			$table->text('location');
			$table->integer('enterprise_id')->unsigned();
			$table->integer('max_capacity')->nullable();
			$table->integer('property_id')->unsigned()->nullable();
			$table->timestamps();
			$table->foreign('enterprise_id')->references('id')->on('enterprises');
			$table->foreign('property_id')->references('id')->on('properties');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('boardrooms');
	}
}
