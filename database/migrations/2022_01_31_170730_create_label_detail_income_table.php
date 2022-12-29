<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLabelDetailIncomeTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('label_detail_income', function (Blueprint $table)
		{
			$table->increments('idlabelDetailIncome');
			$table->integer('idlabels')->unsigned();
			$table->integer('idincomeDetail')->unsigned();
			$table->foreign('idlabels')->references('idlabels')->on('labels');
			$table->foreign('idincomeDetail')->references('idincomeDetail')->on('income_details');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('label_detail_income');
	}
}
