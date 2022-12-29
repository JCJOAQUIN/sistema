<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTicketAnswersTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('ticket_answers', function (Blueprint $table)
		{
			$table->increments('idAnswerTickets');
			$table->text('answer')->nullable();
			$table->datetime('date');
			$table->integer('idTickets')->unsigned();
			$table->integer('users_id')->unsigned();
			$table->foreign('idTickets')->references('idTickets')->on('tickets');
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
		Schema::dropIfExists('ticket_answers');
	}
}
