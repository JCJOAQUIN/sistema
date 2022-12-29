<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRequestHasRequestsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('request_has_requests', function (Blueprint $table)
		{
			$table->increments('id');
			$table->integer('folio')->unsigned();
			$table->integer('children')->unsigned();
			$table->foreign('folio')->references('folio')->on('request_models');
			$table->foreign('children')->references('folio')->on('request_models');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('request_has_requests');
	}
}
