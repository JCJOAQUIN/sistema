<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLoanEnterpriseDocumentsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('loan_enterprise_documents', function (Blueprint $table)
		{
			$table->increments('iddocumentsLoanEnterprise');
			$table->text('path')->nuallble();
			$table->timestamp('date')->nullable();
			$table->integer('idloanEnterprise')->unsigned();
			$table->timestamp('updated_at')->nullable();
			$table->foreign('idloanEnterprise')->references('idloanEnterprise')->on('loan_enterprises');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('loan_enterprise_documents');
	}
}
