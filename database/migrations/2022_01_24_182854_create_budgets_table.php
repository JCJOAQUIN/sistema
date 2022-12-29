<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBudgetsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('budgets', function (Blueprint $table)
		{
			$table->increments('id');
			$table->integer('request_id')->unsigned()->nullable();
			$table->integer('user_id')->unsigned()->nullable();
			$table->tinyInteger('status')->nullable();
			$table->text('comment')->nullable();
			$table->foreign('request_id')->references('folio')->on('request_models');
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
		Schema::dropIfExists('budgets');
	}
}
