<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTicketsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('tickets', function (Blueprint $table)
		{
			$table->increments('idTickets');
			$table->text('subject');
			$table->text('question');
			$table->datetime('request_date');
			$table->integer('request_id')->unsigned();
			$table->integer('assigned_id')->unsigned()->nullable();
			$table->integer('idTypeTickets')->unsigned();
			$table->integer('idPriorityTickets')->unsigned();
			$table->integer('idStatusTickets')->unsigned();
			$table->integer('idSectionTickets')->unsigned();
			$table->foreign('request_id')->references('id')->on('users');
			$table->foreign('assigned_id')->references('id')->on('users');
			$table->foreign('idTypeTickets')->references('idTypeTickets')->on('ticket_types');
			$table->foreign('idPriorityTickets')->references('idPriorityTickets')->on('ticket_priorities');
			$table->foreign('idStatusTickets')->references('idStatusTickets')->on('ticket_statuses');
			$table->foreign('idSectionTickets')->references('idsectionTickets')->on('section_tickets');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('tickets');
	}
}
