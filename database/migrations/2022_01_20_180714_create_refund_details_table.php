<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRefundDetailsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('refund_details', function (Blueprint $table)
		{
			$table->increments('idRefundDetail');
			$table->date('date')->nullable();
			$table->tinyInteger('taxPayment')->nullable();
			$table->string('document',400)->nullable();
			$table->text('concept')->nullable();
			$table->decimal('amount',20,2)->nullable();
			$table->decimal('tax',20,2)->nullable();
			$table->decimal('sAmount',20,2)->nullable();
			$table->integer('idRefund')->unsigned()->nullable();
			$table->integer('idAccount')->unsigned()->nullable();
			$table->integer('idAccountR')->unsigned()->nullable();
			$table->string('typeTax',100)->nullable();
			$table->decimal('quantity',20,2)->nullable();
			$table->tinyInteger('category')->nullable();
			$table->text('code')->nullable();
			$table->text('measurement')->nullable();
			$table->text('unit')->nullable();
			$table->foreign('idRefund')->references('idRefund')->on('refunds');
			$table->foreign('idAccount')->references('idAccAcc')->on('accounts');
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
		Schema::dropIfExists('refund_details');
	}
}
