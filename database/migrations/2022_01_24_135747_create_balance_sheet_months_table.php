<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBalanceSheetMonthsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('balance_sheet_months', function (Blueprint $table)
		{
			$table->increments('id');
			$table->integer('idBalanceSheet')->unsigned();
			$table->string('month',50);
			$table->foreign('idBalanceSheet')->references('id')->on('balance_sheets');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('balance_sheet_months');
	}
}
