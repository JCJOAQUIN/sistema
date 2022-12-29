<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFlightLodgingDetailsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('flight_lodging_details', function (Blueprint $table)
		{
			$table->increments('id');
			$table->integer('flight_lodging_id')->unsigned();
			$table->tinyInteger('type_flight')->comment('1. Sencillo 2.Redondo');
			$table->string('job_position',500)->nullable();
			$table->string('passenger_name',500);
			$table->date('born_date');
			$table->string('airline',500);
			$table->string('route',500);
			$table->date('departure_date');
			$table->time('departure_hour');
			$table->string('airline_back',500)->nullable();
			$table->string('route_back',500)->nullable();
			$table->date('departure_date_back')->nullable();
			$table->time('departure_hour_back')->nullable();
			$table->text('journey_description')->nullable();
			$table->string('direct_superior',500)->nullable();
			$table->date('last_family_journey_date')->nullable();
			$table->string('checked_baggage',500)->nullable();
			$table->string('hosting',500)->nullable();
			$table->date('singin_date')->nullable();
			$table->date('output_date')->nullable();
			$table->decimal('subtotal',20,2)->nullable();
			$table->decimal('iva',20,2)->nullable();
			$table->decimal('retentions',20,2)->nullable();
			$table->decimal('taxes',20,2)->nullable();
			$table->decimal('total',20,2)->nullable();
			$table->timestamps();
			$table->foreign('flight_lodging_id')->references('id')->on('flight_lodgings');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('flight_lodging_details');
	}
}
