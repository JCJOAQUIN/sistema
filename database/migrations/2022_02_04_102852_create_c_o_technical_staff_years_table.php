<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCOTechnicalStaffYearsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('c_o_technical_staff_years', function (Blueprint $table)
		{
			$table->increments('id');
			$table->integer('idUpload')->unsigned()->nullable();
			$table->integer('idConcept')->nullable();
			$table->text('mes')->nullable();
			$table->text('ano')->nullable();
			$table->decimal('amount',24,6)->nullable();
			$table->foreign('idUpload')->references('id')->on('cost_overruns');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('c_o_technical_staff_years');
	}
}
