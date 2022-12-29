<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateResourceDetailTempsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('resource_detail_temps', function (Blueprint $table)
		{
			$table->increments('id');
			$table->text('concept')->nullable();
			$table->decimal('amount',20,2)->nullable();
			$table->integer('idAccAcc')->unsigned()->nullable();
			$table->integer('idResourceTemp')->unsigned()->nullable();
			$table->foreign('idAccAcc')->references('idAccAcc')->on('accounts');
			$table->foreign('idResourceTemp')->references('id')->on('resource_temps');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('resource_detail_temps');
	}
}
