<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWorkOrderDocumentsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('work_order_documents', function (Blueprint $table)
		{
			$table->increments('id');
			$table->text('name')->nullable();
			$table->text('path')->nullable();
			$table->integer('user_id')->unsigned()->nullable();
			$table->integer('idWorkOrder')->unsigned()->nullable();
			$table->timestamp('created')->nullable();
			$table->timestamp('updated_at')->nullable();
			$table->foreign('user_id')->references('id')->on('users');
			$table->foreign('idWorkOrder')->references('id')->on('work_orders');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('work_order_documents');
	}
}
