<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDocumentsPaymentsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('documents_payments', function (Blueprint $table)
		{
			$table->increments('iddocumentsPayments');
			$table->text('path')->nullable();
			$table->integer('idpayment')->unsigned()->nullable();
			$table->timestamp('created')->nullable();
			$table->timestamp('updated_at')->nullable();
			$table->foreign('idpayment')->references('idpayment')->on('payments');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('documents_payments');
	}
}
