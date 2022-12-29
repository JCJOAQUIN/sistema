<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTaxesExpensesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('taxes_expenses', function (Blueprint $table)
		{
			$table->increments('idtaxesExpenses');
			$table->text('name')->nullable();
			$table->decimal('amount',20,2);
			$table->integer('idExpensesDetail')->unsigned();
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
		Schema::dropIfExists('taxes_expenses');
	}
}
