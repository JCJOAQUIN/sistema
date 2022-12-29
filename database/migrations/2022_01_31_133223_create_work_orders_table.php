<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWorkOrdersTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('work_orders', function (Blueprint $table)
		{
			$table->increments('id');
			$table->text('title')->nullable();
			$table->date('elaborate_date')->nullable();
			$table->text('number')->nullable();
			$table->date('date_obra')->nullable();
			$table->integer('idFolio')->unsigned()->nullable();
			$table->integer('urgent')->nullable();
			$table->integer('applicant')->unsigned()->nullable();
			$table->foreign('idFolio')->references('folio')->on('request_models');
			$table->foreign('applicant')->references('id')->on('cat_request_requisitions');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('work_orders');
	}
}
