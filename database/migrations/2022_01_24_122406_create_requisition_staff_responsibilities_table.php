<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRequisitionStaffResponsibilitiesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('requisition_staff_responsibilities', function (Blueprint $table)
		{
			$table->increments('id');
			$table->integer('staff_responsibilities')->unsigned();
			$table->integer('requisition_id')->unsigned();
			$table->foreign('staff_responsibilities','rsr_sr_foreign')->references('id')->on('responsibilities');
			$table->foreign('requisition_id')->references('id')->on('requisitions');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('requisition_staff_responsibilities');
	}
}
