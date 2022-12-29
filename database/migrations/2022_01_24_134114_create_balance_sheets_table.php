<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBalanceSheetsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('balance_sheets', function (Blueprint $table)
		{
			$table->increments('id');
			$table->tinyInteger('status')->nullable()->comment('0 - en Cola, 1 - Generado');
			$table->tinyInteger('type')->nullable()->comment('1 - anual, 2 - mensual');
			$table->integer('users_id')->unsigned()->nullable();
			$table->date('date')->nullable();
			$table->text('file')->nullable();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('balance_sheets');
	}
}
