<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCreditCardDocumentsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('credit_card_documents', function (Blueprint $table)
		{
			$table->increments('id');
			$table->integer('idcreditCard')->unsigned()->nullable();
			$table->text('path')->nullable();
			$table->timestamp('date')->nullable();
			$table->timestamp('updated_at')->nullable();
			$table->foreign('idcreditCard')->references('idcreditCard')->on('credit_cards');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('credit_card_documents');
	}
}
