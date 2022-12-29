<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePurchaseEnterpriseDocumentsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('purchase_enterprise_documents', function (Blueprint $table)
		{
			$table->increments('idpurchaseEnterpriseDocuments');
			$table->text('path')->nullable();
			$table->timestamp('date')->nullable();
			$table->integer('idpurchaseEnterprise')->unsigned();
			$table->timestamp('updated_at')->nullable();
			$table->foreign('idpurchaseEnterprise')->references('idpurchaseEnterprise')->on('purchase_enterprises');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('purchase_enterprise_documents');
	}
}
