<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePropertiesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('properties', function (Blueprint $table)
		{
			$table->increments('id');
			$table->text('property')->nullable();
			$table->text('location')->nullable();
			$table->text('type_property')->nullable();
			$table->text('use_property')->nullable();
			$table->text('number_of_rooms')->nullable();
			$table->text('number_of_bathrooms')->nullable();
			$table->text('parking_lot')->nullable();
			$table->text('kitchen_room')->nullable();
			$table->text('garden')->nullable();
			$table->text('boardroom')->nullable();
			$table->text('furnished')->nullable();
			$table->text('measurements')->nullable();
			$table->integer('users_id')->unsigned()->nullable();
			$table->timestamps();
			$table->foreign('users_id')->references('id')->on('users');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('properties');
	}
}
