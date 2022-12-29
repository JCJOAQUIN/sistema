<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRetentionIncomesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('retention_incomes', function (Blueprint $table)
		{
			$table->increments('idretentionIncome');
			$table->text('name')->nullable();
			$table->decimal('amount',20,2);
			$table->integer('idincomeDetail')->unsigned()->nullable();
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
		Schema::dropIfExists('retention_incomes');
	}
}
