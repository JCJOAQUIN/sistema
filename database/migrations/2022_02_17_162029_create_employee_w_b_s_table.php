<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmployeeWBSTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('employee_w_b_s', function (Blueprint $table)
		{
			$table->increments('id');
			$table->integer('working_data_id')->unsigned();
			$table->integer('cat_code_w_bs_id')->unsigned();
			$table->timestamps();
			$table->foreign('working_data_id')->references('id')->on('worker_datas');
			$table->foreign('cat_code_w_bs_id')->references('id')->on('cat_code_w_bs');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('employee_w_b_s');
	}
}
