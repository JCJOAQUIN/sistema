<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExpensesDocumentsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('expenses_documents', function (Blueprint $table)
		{
			$table->increments('idExpensesDocuments');
			$table->string('name',250)->nullable();
			$table->text('path')->nullable();
			$table->date('date')->nullable();
			$table->integer('idExpensesDetail')->unsigned()->nullable();
			$table->string('fiscal_folio',250)->nullable();
			$table->date('datepath')->nullable();
			$table->time('timepath')->nullable();
			$table->string('ticket_number',250)->nullable();
			$table->decimal('amount',20,6)->nullable();
			$table->integer('users_id')->unsigned()->nullable();
			$table->timestamps();
			$table->foreign('idExpensesDetail')->references('idExpensesDetail')->on('expenses_details');
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
		Schema::dropIfExists('expenses_documents');
	}
}
