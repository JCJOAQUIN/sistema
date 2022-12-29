<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDocumentsTicketsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('documents_tickets', function (Blueprint $table)
		{
			$table->increments('iddocumentsTickets');
			$table->text('path')->nullable();
			$table->integer('idTickets')->unsigned()->nullable();
			$table->integer('idAnswerTickets')->unsigned()->nullable();
			$table->Foreign('idTickets')->references('idTickets')->on('tickets');
			$table->Foreign('idAnswerTickets')->references('idAnswerTickets')->on('ticket_answers');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('documents_tickets');
	}
}
