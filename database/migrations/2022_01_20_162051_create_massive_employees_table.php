<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMassiveEmployeesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('massive_employees', function (Blueprint $table)
		{
			$table->increments('id');
			$table->integer('idEmployee')->unsigned();
			$table->integer('idCreator')->unsigned();
			$table->string('csv',600);
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('massive_employees');
	}
}
