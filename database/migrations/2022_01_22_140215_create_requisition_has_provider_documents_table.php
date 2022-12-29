<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRequisitionHasProviderDocumentsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('requisition_has_provider_documents', function (Blueprint $table)
		{
			$table->increments('id');
			$table->text('name')->nullable();
			$table->text('path')->nullable();
			$table->integer('user_id')->unsigned()->nullable();
			$table->integer('idRequisitionHasProvider')->unsigned()->nullable();
			$table->timestamp('created')->nullable();
			$table->timestamp('updated_at')->nullable();
			$table->foreign('user_id')->references('id')->on('users');
			$table->foreign('idRequisitionHasProvider','rhpd_id_rhp_foreign')->references('id')->on('requisition_has_providers');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('requisition_has_provider_documents');
	}
}
