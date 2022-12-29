<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('users', function (Blueprint $table)
		{
			$table->increments('id');
			$table->string('name');
			$table->string('last_name');
			$table->string('scnd_last_name')->nullable();
			$table->enum('gender',['hombre','mujer']);
			$table->string('phone')->nullable();
			$table->string('extension')->nullable();
			$table->string('email')->nullable();
			$table->string('password')->nullable();
			$table->enum('status',['ACTIVE','NO-MAIL','DELETED','SUSPENDED','RE-ENTRY','RE-ENTRY-NO-MAIL']);
			$table->integer('role_id')->unsigned()->nullable();
			$table->integer('area_id')->unsigned()->nullable();
			$table->integer('departament_id')->unsigned()->nullable();
			$table->string('position',500)->nullable();
			$table->tinyInteger('cash')->default(0);
			$table->decimal('cash_amount',16,2)->nullable();
			$table->rememberToken();
			$table->timestamps();
			$table->tinyInteger('sys_user')->default(0);
			$table->tinyInteger('active')->default(1);
			$table->tinyInteger('notification')->default(1);
			$table->tinyInteger('requisitionVote')->default(0);
			$table->tinyInteger('requisitionSend')->default(0);
			$table->tinyInteger('adglobal')->default(0);
			$table->integer('real_employee_id')->unsigned()->nullable();
			$table->foreign('role_id')->references('id')->on('roles');
			$table->foreign('area_id')->references('id')->on('areas');
			$table->foreign('departament_id')->references('id')->on('departments');
			$table->foreign('real_employee_id')->references('id')->on('real_employees');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('users');
	}
}
