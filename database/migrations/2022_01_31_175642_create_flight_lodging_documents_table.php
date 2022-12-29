<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFlightLodgingDocumentsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('flight_lodging_documents', function (Blueprint $table)
		{
			$table->increments('id');
			$table->string('name',500);
			$table->text('path');
			$table->timestamp('date')->nullable();
			$table->integer('users_id')->unsigned();
			$table->integer('flight_lodging_id')->unsigned();
			$table->foreign('users_id')->references('id')->on('users');
			$table->foreign('flight_lodging_id')->references('id')->on('flight_lodgings');
			$table->timestamp('updated_at')->nullable();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('flight_lodging_documents');
	}
}
