<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRequisitionDocumentsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('requisition_documents', function (Blueprint $table)
		{
			$table->increments('id');
			$table->text('name')->nullable();
			$table->text('path')->nullable();
			$table->string('fiscal_folio',250)->nullable();
			$table->date('datepath')->nullable();
			$table->time('timepath')->nullable();
			$table->string('ticket_number',250)->nullable();
			$table->decimal('amount',20,2)->nullable();
			$table->integer('user_id')->unsigned()->nullable();
			$table->integer('idRequisition')->unsigned()->nullable();
			$table->timestamp('created')->nullable();
			$table->timestamp('updated_at')->nullable();
			$table->foreign('user_id')->references('id')->on('users');
			$table->foreign('idRequisition')->references('id')->on('requisitions');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('requisition_documents');
	}
}
