<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdministrativeBudgetDetailsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('administrative_budget_details', function (Blueprint $table)
		{
			$table->increments('id');
			$table->text('account')->nullable();
			$table->integer('account_id')->unsigned()->nullable();
			$table->decimal('amount',20,2)->nullable();
			$table->decimal('amount_spent',20,2)->nullable();
			$table->decimal('alert_percent',20,2)->nullable();
			$table->tinyInteger('status')->default(0);
			$table->integer('idAdministrativeBudget')->unsigned()->nullable();
			$table->timestamps();
			$table->foreign('account_id')->references('idAccAcc')->on('accounts');
			$table->foreign('idAdministrativeBudget')->references('id')->on('administrative_budgets');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('administrative_budget_details');
	}
}
