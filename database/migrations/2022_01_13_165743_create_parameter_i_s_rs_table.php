<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateParameterISRsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('parameter_i_s_rs', function (Blueprint $table)
		{
			$table->increments('id');
			$table->double('inferior');
			$table->double('superior')->nullable();
			$table->double('quota');
			$table->double('excess');
			$table->integer('lapse');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('parameter_i_s_rs');
	}
}
