<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateParametersTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('parameters', function (Blueprint $table)
		{
			$table->string('parameter_name')->primary()->comment('* Mayúsculas * Sin acentos * Sin espacios * Separaciones con guión bajo');
			$table->text('description');
			$table->text('category');
			$table->text('sub_category');
			$table->string('parameter_value',100);
			$table->string('prefix',100)->nullable();
			$table->string('suffix',100)->nullable();
			$table->string('validation',100);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('parameters');
	}
}
