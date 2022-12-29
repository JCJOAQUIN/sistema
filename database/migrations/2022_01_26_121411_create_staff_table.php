<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStaffTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('staff', function (Blueprint $table)
		{
			$table->increments('idStaff');
			$table->integer('idFolio')->unsigned();
			$table->integer('idKind')->unsigned();
			$table->text('title')->nullable();
			$table->date('datetitle')->nullable();
			$table->integer('boss')->unsigned()->nullable();
			$table->string('schedule_start',45)->nullable();
			$table->string('schedule_end',45)->nullable();
			$table->decimal('minSalary',20,2)->nullable();
			$table->decimal('maxSalary',20,2)->nullable();
			$table->text('reason')->nullable();
			$table->integer('role_id')->unsigned()->nullable();
			$table->text('position')->nullable();
			$table->text('periodicity')->nullable();
			$table->text('description')->nullable();
			$table->text('habilities')->nullable();
			$table->text('experience')->nullable();
			$table->foreign('idFolio')->references('folio')->on('request_models');
			$table->foreign('idKind')->references('idrequestkind')->on('request_kinds');
			$table->foreign('boss')->references('id')->on('users');
			$table->foreign('role_id')->references('id')->on('roles');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('staff');
	}
}
