<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePropertyPaymentsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('property_payments', function (Blueprint $table)
		{
			$table->increments('id');
			$table->string('payment_type',250);
			$table->string('periodicity',100);
			$table->string('date_range',250);
			$table->decimal('amount',20,2);
			$table->string('path',250);
			$table->integer('property_id')->unsigned();
			$table->integer('user_id')->unsigned();
			$table->timestamps();
			$table->foreign('property_id')->references('id')->on('properties');
			$table->foreign('user_id')->references('id')->on('users');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('property_payments');
	}
}
