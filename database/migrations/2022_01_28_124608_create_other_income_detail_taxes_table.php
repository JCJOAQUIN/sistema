<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOtherIncomeDetailTaxesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('other_income_detail_taxes', function (Blueprint $table)
		{
			$table->increments('id');
			$table->text('description')->nullable();
			$table->decimal('total',20,2)->nullable();
			$table->string('type',50)->nullable();
			$table->integer('idOtherIncomeDetail')->unsigned()->nullable();
			$table->foreign('idOtherIncomeDetail')->references('id')->on('other_income_details');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('other_income_detail_taxes');
	}
}
