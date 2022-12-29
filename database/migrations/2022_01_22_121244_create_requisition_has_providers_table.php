<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRequisitionHasProvidersTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('requisition_has_providers', function (Blueprint $table)
		{
			$table->increments('id');
			$table->integer('idProviderSecondary')->unsigned()->nullable();
			$table->integer('idRequisition')->unsigned()->nullable();
			$table->integer('user_id')->unsigned()->nullable();
			$table->text('commentaries')->nullable();
			$table->text('type_currency')->nullable();
			$table->text('delivery_time')->nullable();
			$table->text('credit_time')->nullable();
			$table->text('guarantee')->nullable();
			$table->text('spare')->nullable();
			$table->foreign('idProviderSecondary')->references('id')->on('provider_secondaries');
			$table->foreign('idRequisition')->references('id')->on('requisitions');
			$table->foreign('user_id')->references('id')->on('users');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('requisition_has_providers');
	}
}
