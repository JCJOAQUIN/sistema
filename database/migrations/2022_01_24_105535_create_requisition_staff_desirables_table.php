<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRequisitionStaffDesirablesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('requisition_staff_desirables', function (Blueprint $table)
		{
			$table->increments('id');
			$table->text('desirable')->nullable();
			$table->text('description')->nullable();
			$table->integer('requisition_id')->nullable()->constrained();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('requisition_staff_desirables');
	}
}
