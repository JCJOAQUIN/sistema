<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDocumentsIncomesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('documents_incomes', function (Blueprint $table)
		{
			$table->increments('id');
			$table->text('path')->nullable();
			$table->string('name', 500)->nullable();
			$table->integer('idIncome')->unsigned()->nullable();
			$table->timestamps();
			$table->foreign('idIncome')->references('idIncome')->on('incomes');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('documents_incomes');
	}
}
