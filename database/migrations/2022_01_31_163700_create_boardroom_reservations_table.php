<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBoardroomReservationsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('boardroom_reservations', function (Blueprint $table)
		{
			$table->increments('id');
			$table->date('start');
			$table->date('end');
			$table->text('cancel_description')->nullable();
			$table->integer('boardroom_id')->unsigned();
			$table->text('reason')->nullable();
			$table->text('observations')->nullable();
			$table->integer('id_request')->unsigned();
			$table->integer('id_elaborate')->unsigned();
			$table->tinyInteger('status')->default(1);
			$table->timestamps();
			$table->foreign('boardroom_id')->references('id')->on('boardrooms');
			$table->foreign('id_request')->references('id')->on('users');
			$table->foreign('id_elaborate')->references('id')->on('users');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('boardroom_reservations');
	}
}
