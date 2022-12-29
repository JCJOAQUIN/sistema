<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateResourceTempsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('resource_temps', function (Blueprint $table)
		{
			$table->increments('id');
			$table->text('title')->nullable();
			$table->decimal('total',20,2)->nullable();
			$table->text('reference')->nullable();
			$table->string('currency',100)->nullable();
			$table->integer('idEmployee')->unsigned()->nullable();
			$table->integer('idpaymentMethod')->unsigned()->nullable();
			$table->integer('idAutomaticRequests')->unsigned()->nullable();
			$table->foreign('idEmployee')->references('idEmployee')->on('employees');
			$table->foreign('idpaymentMethod')->references('idpaymentMethod')->on('payment_methods');
			$table->foreign('idAutomaticRequests')->references('id')->on('automatic_requests');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('resource_temps');
	}
}
