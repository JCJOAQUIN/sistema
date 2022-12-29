<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVehicleFinesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('vehicle_fines', function (Blueprint $table)
		{
			$table->increments('id');
			$table->integer('real_employee_id')->unsigned();
			$table->string('status',100)->nullable();
			$table->date('date');
			$table->date('payment_date')->nullable();
			$table->date('payment_limit_date')->nullable();
			$table->decimal('total',20,2);
			$table->integer('vehicles_id')->unsigned();
			$table->integer('users_id')->unsigned();
			$table->timestamps();
			$table->foreign('real_employee_id')->references('id')->on('real_employees');
			$table->foreign('vehicles_id')->references('id')->on('vehicles');
			$table->foreign('users_id')->references('id')->on('users');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('vehicle_fines');
	}
}
