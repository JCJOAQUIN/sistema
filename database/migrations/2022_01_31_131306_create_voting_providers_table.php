<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVotingProvidersTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('voting_providers', function (Blueprint $table)
		{
			$table->increments('id');
			$table->integer('user_id')->unsigned();
			$table->integer('enterprise_id')->unsigned()->nullable();
			$table->integer('idRequisitionHasProvider')->unsigned()->nullable();
			$table->integer('idRequisitionDetail')->unsigned();
			$table->integer('idRequisition')->unsigned();
			$table->text('commentaries')->nullable();
			$table->foreign('user_id')->references('id')->on('users');
			$table->foreign('enterprise_id')->references('id')->on('enterprises');
			$table->foreign('idRequisitionHasProvider')->references('id')->on('requisition_has_providers');
			$table->foreign('idRequisitionDetail')->references('id')->on('requisition_details');
			$table->foreign('idRequisition')->references('id')->on('requisitions');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('voting_providers');
	}
}
