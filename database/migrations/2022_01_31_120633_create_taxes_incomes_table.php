<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTaxesIncomesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('taxes_incomes', function (Blueprint $table)
		{
			$table->increments('idtaxesIncome');
			$table->text('name')->nullable();
			$table->decimal('amount',20,2);
			$table->integer('idincomeDetail')->unsigned();
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
		Schema::dropIfExists('taxes_incomes');
	}
}
