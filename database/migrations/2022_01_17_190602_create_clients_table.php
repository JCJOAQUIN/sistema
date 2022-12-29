<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClientsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('clients', function (Blueprint $table)
		{
			$table->increments('idClient');
			$table->text('businessName')->nullable();
			$table->text('email')->nullable();
			$table->string('phone',45)->nullable();
			$table->string('rfc',20)->nullable();
			$table->text('contact')->nullable();
			$table->text('commentaries')->nullable();
			$table->tinyInteger('status')->default(0)->comment('0. Incompleto, 1. Baja, 2. Disponible');
			$table->integer('users_id')->unsigned();
			$table->text('address')->nullable();
			$table->string('number',45)->nullable();
			$table->text('colony')->nullable();
			$table->string('postalCode',45)->nullable();
			$table->text('city')->nullable();
			$table->integer('state_idstate')->unsigned()->nullable();
			$table->timestamp('created');
			$table->foreign('users_id')->references('id')->on('users');
			$table->foreign('state_idstate')->references('idstate')->on('states');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('clients');
	}
}
