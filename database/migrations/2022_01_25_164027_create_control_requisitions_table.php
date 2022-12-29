<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateControlRequisitionsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('control_requisitions', function (Blueprint $table)
		{
			$table->increments('id');
			$table->text('data_remittances')->nullable();
			$table->text('cost_center')->nullable();
			$table->text('WBS')->nullable();
			$table->text('frentes')->nullable();
			$table->text('EDT')->nullable();
			$table->text('cost_type')->nullable();
			$table->text('cost_description')->nullable();
			$table->text('work_area')->nullable();
			$table->text('data_requisition')->nullable();
			$table->text('requisition')->nullable();
			$table->text('applicant')->nullable();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('control_requisitions');
	}
}
