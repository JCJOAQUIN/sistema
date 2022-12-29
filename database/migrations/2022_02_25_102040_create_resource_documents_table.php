<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateResourceDocumentsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('resource_documents', function (Blueprint $table)
		{
			$table->increments('id');
			$table->text('name');
			$table->text('path');
			$table->string('fiscal_folio',250)->nullable();
			$table->date('datepath')->nullable();
			$table->time('timepath')->nullable();
			$table->string('ticket_number', 250)->nullable();
			$table->decimal('amount',20,2)->nullable();
			$table->integer('user_id')->unsigned();
			$table->integer('resource_id')->unsigned();
			$table->timestamps();
			$table->foreign('user_id')->references('id')->on('users');
			$table->foreign('resource_id')->references('idresource')->on('resources');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('resource_documents');
	}
}
