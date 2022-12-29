<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBillNominaExtraHoursTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('bill_nomina_extra_hours', function (Blueprint $table)
		{
			$table->increments('id');
			$table->integer('days');
			$table->integer('hours');
			$table->double('amount',20,2);
			$table->string('cat_type_hour_id',5);
			$table->integer('bill_nomina_id')->unsigned();
			$table->foreign('cat_type_hour_id')->references('id')->on('cat_type_hours');
			$table->foreign('bill_nomina_id')->references('id')->on('bill_nominas');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('bill_nomina_extra_hours');
	}
}
