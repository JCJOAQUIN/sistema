<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDocumentsPurchasesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('documents_purchases', function (Blueprint $table)
		{
			$table->increments('iddocumentsPurchase');
			$table->integer('idPurchase')->unsigned()->nullable();
			$table->text('path')->nullable();
			$table->timestamp('date')->nullable();
			$table->text('name')->nullable();
			$table->string('fiscal_folio',255)->nullable();
			$table->string('ticket_number',255)->nullable();
			$table->decimal('amount', 20, 2)->nullable();
			$table->time('timepath')->nullable();
			$table->date('datepath')->nullable();
			$table->timestamp('updated_at')->nullable();
			$table->foreign('idPurchase')->references('idPurchase')->on('purchases');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('documents_purchases');
	}
}
