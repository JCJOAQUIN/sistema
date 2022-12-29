<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePurchaseEnterpriseDetailLabelsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('purchase_enterprise_detail_labels', function (Blueprint $table)
		{
			$table->increments('idPurchaseEnterpriseDetailLabel');
			$table->integer('idlabels')->unsigned();
			$table->integer('idPurchaseEnterpriseDetail')->unsigned();
			$table->foreign('idlabels')->references('idlabels')->on('labels');
			$table->foreign('idPurchaseEnterpriseDetail','pedl_idpurchaseenterprisedetail_foreign')->references('idPurchaseEnterpriseDetail')->on('purchase_enterprise_details');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('purchase_enterprise_detail_labels');
	}
}
