<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOtherIncomeDetailsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('other_income_details', function (Blueprint $table)
		{
			$table->increments('id');
			$table->decimal('quantity',20,2)->nullable();
			$table->text('description')->nullable();
			$table->text('unit')->nullable();
			$table->decimal('unit_price',20,2)->nullable();
			$table->string('type_tax',10)->nullable();
			$table->decimal('tax',20,2)->nullable();
			$table->decimal('total_taxes',20,2)->nullable();
			$table->decimal('total_retentions',20,2)->nullable();
			$table->decimal('subtotal',20,2)->nullable();
			$table->decimal('total',20,2)->nullable();
			$table->integer('idOtherIncome')->unsigned()->nullable();
			$table->foreign('idOtherIncome')->references('id')->on('other_incomes');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('other_income_details');
	}
}
