<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIncomeDetailsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('income_details', function (Blueprint $table)
		{
			$table->increments('idincomeDetail');
			$table->integer('idIncome')->unsigned();
			$table->decimal('quantity', 20, 2)->nullable();
			$table->text('unit')->nullable();
			$table->text('description')->nullable();
			$table->decimal('unitPrice', 20, 2)->nullable();
			$table->decimal('tax', 20, 2)->nullable();
			$table->decimal('discount', 20, 2)->nullable();
			$table->decimal('amount', 20, 2)->nullable();
			$table->string('typeTax',100)->nullable();
			$table->decimal('subtotal', 20, 2)->nullable();
			$table->foreign('idIncome')->references('idIncome')->on('incomes');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('income_details');
	}
}
