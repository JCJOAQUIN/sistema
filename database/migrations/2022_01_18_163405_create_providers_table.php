<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProvidersTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('providers', function (Blueprint $table)
		{
			$table->increments('idProvider');
			$table->integer('provider_data_id')->unsigned()->nullable();
			$table->text('businessName')->nullable();
			$table->text('beneficiary')->nullable();
			$table->string('phone',45)->nullable();
			$table->text('rfc')->nullable();
			$table->text('contact')->nullable();
			$table->text('commentaries')->nullable();
			$table->tinyInteger('status')->default(0);
			$table->integer('users_id')->unsigned()->nullable();
			$table->text('address')->nullable();
			$table->string('number',45)->nullable();
			$table->text('colony')->nullable();
			$table->string('postalCode',45)->nullable();
			$table->text('city')->nullable();
			$table->integer('state_idstate')->unsigned()->nullable();
			$table->timestamp('created');
			$table->foreign('provider_data_id')->references('id')->on('provider_datas');
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
		Schema::dropIfExists('providers');
	}
}
