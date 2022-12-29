<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExpensesDetailsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('expenses_details', function (Blueprint $table)
		{
			$table->increments('idExpensesDetail');
			$table->integer('idExpenses')->unsigned();
			$table->date('RefundDate')->nullable();
			$table->tinyInteger('taxPayment')->nullable();
			$table->string('document',450)->nullable();
			$table->text('concept')->nullable();
			$table->decimal('amount',20,2)->nullable();
			$table->decimal('tax',20,2)->nullable();
			$table->decimal('sAmount',20,2)->nullable();
			$table->integer('idAccount')->unsigned()->nullable();
			$table->integer('idresourcedetail')->unsigned()->nullable();
			$table->integer('idAccountR')->unsigned()->nullable();
			$table->string('typeTax',100)->nullable();
			$table->foreign('idExpenses')->references('idExpenses')->on('expenses');
			$table->foreign('idAccount')->references('idAccAcc')->on('accounts');
			$table->foreign('idresourcedetail')->references('idresourcedetail')->on('resource_details');
			$table->foreign('idAccountR')->references('idAccAcc')->on('accounts');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('expenses_details');
	}
}
