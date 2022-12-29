<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStaffResponsibilitiesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('staff_responsibilities', function (Blueprint $table)
		{
			$table->integer('idStaff')->unsigned();
			$table->integer('idResponsibility')->unsigned();
			$table->foreign('idStaff')->references('idStaff')->on('staff');
			$table->foreign('idResponsibility')->references('id')->on('responsibilities');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('staff_responsibilities');
	}
}
