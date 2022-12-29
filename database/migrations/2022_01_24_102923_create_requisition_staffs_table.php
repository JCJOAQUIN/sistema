<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRequisitionStaffsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('requisition_staffs', function (Blueprint $table)
		{
			$table->increments('id');
			$table->integer('boss_id')->unsigned()->nullable();
			$table->text('staff_reason')->nullable();
			$table->text('staff_position')->nullable();
			$table->string('staff_periodicity',500)->nullable();
			$table->string('staff_schedule_start',200)->nullable();
			$table->string('staff_schedule_end',200)->nullable();
			$table->decimal('staff_min_salary',20,2)->nullable();
			$table->decimal('staff_max_salary',20,2)->nullable();
			$table->text('staff_s_description')->nullable();
			$table->text('staff_habilities')->nullable();
			$table->text('staff_experience')->nullable();
			$table->integer('requisition_id')->unsigned()->nullable();
			$table->foreign('boss_id')->references('id')->on('users');
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
		Schema::dropIfExists('requisition_staffs');
	}
}
