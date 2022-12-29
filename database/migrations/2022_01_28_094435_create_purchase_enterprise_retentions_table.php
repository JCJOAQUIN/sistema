<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePurchaseEnterpriseRetentionsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('purchase_enterprise_retentions', function (Blueprint $table)
		{
			$table->increments('idpurchaseEnterpriseRetention');
			$table->string('name',500)->nullable();
			$table->decimal('amount',20,2)->nullable();
			$table->integer('idPurchaseEnterpriseDetail')->unsigned()->nullable();
			$table->foreign('idPurchaseEnterpriseDetail','per_idpurchaseenterprisedetail_foreign')->references('idPurchaseEnterpriseDetail')->on('purchase_enterprise_details');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('purchase_enterprise_retentions');
	}
}
