<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateParameterSubsidiesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('parameter_subsidies', function (Blueprint $table)
		{
			$table->increments('id');
			$table->decimal('inferior',20,2);
			$table->decimal('superior',20,2)->nullable();
			$table->decimal('subsidy',20,2);
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
		Schema::dropIfExists('parameter_subsidies');
	}
}
