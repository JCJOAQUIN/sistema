<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateControlBanksTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('control_banks', function (Blueprint $table)
		{
			$table->increments('id');
			$table->text('data')->nullable();
			$table->text('TRASF_CH')->nullable();
			$table->text('amount')->nullable();
			$table->text('observations')->nullable();
			$table->text('note')->nullable();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('control_banks');
	}
}
