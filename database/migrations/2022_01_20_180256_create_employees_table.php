<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmployeesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('employees', function (Blueprint $table)
		{
			$table->increments('idEmployee');
			$table->text('alias')->nullable();
			$table->string('clabe',100)->nullable();
			$table->string('account',100)->nullable();
			$table->string('cardNumber',100)->nullable();
			$table->integer('idBanks')->unsigned()->nullable();
			$table->integer('idKindOfBank')->unsigned()->nullable();
			$table->integer('idUsers')->unsigned();
			$table->tinyInteger('visible')->default(1);
			$table->foreign('idBanks')->references('idBanks')->on('banks');
			$table->foreign('idKindOfBank')->references('idKindOfBank')->on('kind_of_banks');
			$table->foreign('idUsers')->references('id')->on('users');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('employees');
	}
}
