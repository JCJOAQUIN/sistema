<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLabelDetailExpensesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('label_detail_expenses', function (Blueprint $table)
		{
			$table->increments('idlabelDetailExpenses');
			$table->integer('idlabels')->unsigned();
			$table->integer('idExpensesDetail')->unsigned();
			$table->foreign('idlabels')->references('idlabels')->on('labels');
			$table->foreign('idExpensesDetail')->references('idExpensesDetail')->on('expenses_details');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('label_detail_expenses');
	}
}
