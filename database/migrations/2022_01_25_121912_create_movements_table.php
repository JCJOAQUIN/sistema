<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMovementsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('movements', function (Blueprint $table)
		{
			$table->increments('idmovement');
			$table->dateTime('movementDate')->nullable();
			$table->decimal('amount',20,2)->nullable();
			$table->text('description')->nullable();
			$table->text('commentaries')->nullable();
			$table->tinyInteger('statusConciliation')->default(0);
			$table->integer('idEnterprise')->unsigned();
			$table->integer('idAccount')->unsigned();
			$table->integer('idpayment')->unsigned()->nullable();
			$table->dateTime('conciliationDate')->nullable();
			$table->string('movementType', 50)->nullable();
			$table->integer('creator')->unsigned()->nullable();
			$table->timestamp('date_creator')->nullable();
			$table->integer('idBill')->unsigned()->nullable();
			$table->timestamp('updated_at')->nullable();
			$table->foreign('idEnterprise')->references('id')->on('enterprises');
			$table->foreign('idAccount')->references('idAccAcc')->on('accounts');
			$table->foreign('idpayment')->references('idpayment')->on('payments');
			$table->foreign('creator')->references('id')->on('users');
			$table->foreign('idBill')->references('idBill')->on('bills');
		});

		Schema::table('payments', function (Blueprint $table)
		{
			$table->foreign('idmovement')->references('idmovement')->on('movements');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('payments', function (Blueprint $table)
		{
			$table->dropForeign(['idmovement']);
		});

		Schema::dropIfExists('movements');
	}
}
