<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRefundDocumentsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('refund_documents', function (Blueprint $table)
		{
			$table->increments('idRefundDocuments');
			$table->text('path')->nullable();
			$table->date('date')->nullable();
			$table->integer('idRefundDetail')->unsigned()->nullable();
			$table->string('fiscal_folio',255)->nullable();
			$table->string('ticket_number',255)->nullable();
			$table->decimal('amount',20,2)->nullable();
			$table->time('timepath')->nullable();
			$table->date('datepath')->nullable();
			$table->string('name',200)->nullable();
			$table->foreign('idRefundDetail')->references('idRefundDetail')->on('refund_details');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('refund_documents');
	}
}
