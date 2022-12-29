<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRequisitionStaffFunctionsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('requisition_staff_functions', function (Blueprint $table)
		{
			$table->increments('id');
			$table->text('function');
			$table->text('description');
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
		Schema::dropIfExists('requisition_staff_functions');
	}
}
