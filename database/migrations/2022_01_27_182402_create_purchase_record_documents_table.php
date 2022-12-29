<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePurchaseRecordDocumentsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('purchase_record_documents', function (Blueprint $table)
		{
			$table->increments('id');
			$table->integer('idPurchaseRecord')->unsigned();
			$table->text('path')->nullable();
			$table->timestamp('date')->nullable();
			$table->text('name')->nullable();
			$table->string('fiscal_folio',255)->nullable();
			$table->string('ticket_number',255)->nullable();
			$table->decimal('amount',20,2)->nullable();
			$table->time('timepath')->nullable();
			$table->date('datepath')->nullable();
			$table->timestamp('updated_at')->nullable();
			$table->foreign('idPurchaseRecord')->references('id')->on('purchase_records');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('purchase_record_documents');
	}
}
