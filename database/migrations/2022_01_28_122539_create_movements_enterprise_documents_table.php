<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMovementsEnterpriseDocumentsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('movements_enterprise_documents', function (Blueprint $table)
		{
			$table->increments('idmovementsEnterpriseDocuments');
			$table->text('path')->nullable();
			$table->timestamp('date')->nullable();
			$table->integer('idmovementsEnterprise')->unsigned();
			$table->timestamp('updated_at')->nullable();
			$table->foreign('idmovementsEnterprise')->references('idmovementsEnterprise')->on('movements_enterprises');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('movements_enterprise_documents');
	}
}
