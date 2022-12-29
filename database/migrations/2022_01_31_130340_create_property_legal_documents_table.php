<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePropertyLegalDocumentsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('property_legal_documents', function (Blueprint $table)
		{
			$table->increments('id');
			$table->string('legal_document',500)->nullable();
			$table->string('path',250)->nullable();
			$table->text('description')->nullable();
			$table->integer('property_id')->unsigned();
			$table->integer('user_id')->unsigned();
			$table->timestamps();
			$table->foreign('property_id')->references('id')->on('properties');
			$table->foreign('user_id')->references('id')->on('users');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('property_legal_documents');
	}
}
