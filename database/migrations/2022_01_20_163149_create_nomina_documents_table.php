<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNominaDocumentsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('nomina_documents', function (Blueprint $table)
		{
			$table->increments('id');
			$table->string('name',500);
			$table->text('path');
			$table->integer('idnominaEmployee')->unsigned()->nullable();
			$table->integer('users_id')->unsigned()->nullable();
			$table->timestamps();
			$table->foreign('idnominaEmployee')->references('idnominaEmployee')->on('nomina_employees');
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
		Schema::dropIfExists('nomina_documents');
	}
}
