<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStaffFunctionsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('staff_functions', function (Blueprint $table)
		{
			$table->increments('id');
			$table->integer('idStaff')->unsigned();
			$table->string('function',300);
			$table->string('description',500);
			$table->foreign('idStaff')->references('idStaff')->on('staff');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('staff_functions');
	}
}
