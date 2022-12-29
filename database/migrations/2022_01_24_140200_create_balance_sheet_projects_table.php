<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBalanceSheetProjectsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('balance_sheet_projects', function (Blueprint $table)
		{
			$table->increments('id');
			$table->integer('idBalanceSheet')->unsigned();
			$table->integer('idProject')->unsigned();
			$table->foreign('idBalanceSheet')->references('id')->on('balance_sheets');
			$table->foreign('idProject')->references('idproyect')->on('projects');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('balance_sheet_projects');
	}
}
