<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateResourceDetailsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('resource_details', function (Blueprint $table)
		{
			$table->increments('idresourcedetail');
			$table->text('concept')->nullable();
			$table->decimal('amount',20,2)->nullable();
			$table->integer('idAccAcc')->unsigned();
			$table->integer('idresource')->unsigned();
			$table->integer('idAccAccR')->unsigned()->nullable();
			$table->tinyInteger('statusRefund')->default(0);
			$table->foreign('idAccAcc')->references('idAccAcc')->on('accounts');
			$table->foreign('idresource')->references('idresource')->on('resources');
			$table->foreign('idAccAccR')->references('idAccAcc')->on('accounts');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('resource_details');
	}
}
