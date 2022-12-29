<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRequestHasReclassificationsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('request_has_reclassifications', function (Blueprint $table)
		{
			$table->increments('id');
			$table->integer('folio')->unsigned()->nullable();
			$table->integer('kind')->unsigned()->nullable();
			$table->integer('idEnterprise')->unsigned()->nullable();
			$table->integer('idDepartment')->unsigned()->nullable();
			$table->integer('idArea')->unsigned()->nullable();
			$table->integer('idProject')->unsigned()->nullable();
			$table->integer('idAccAcc')->unsigned()->nullable();
			$table->integer('idresourcedetail')->unsigned()->nullable();
			$table->integer('idRefundDetail')->unsigned()->nullable();
			$table->integer('idExpensesDetail')->unsigned()->nullable();
			$table->integer('idUser')->unsigned()->nullable();
			$table->datetime('date')->nullable();
			$table->text('commentaries')->nullable();
			$table->integer('idEnterpriseOrigin')->unsigned()->nullable();
			$table->integer('idEnterpriseDestiny')->unsigned()->nullable();
			$table->integer('idAreaOrigin')->unsigned()->nullable();
			$table->integer('idAreaDestiny')->unsigned()->nullable();
			$table->integer('idDepartmentOrigin')->unsigned()->nullable();
			$table->integer('idDepartmentDestiny')->unsigned()->nullable();
			$table->integer('idProjectOrigin')->unsigned()->nullable();
			$table->integer('idProjectDestiny')->unsigned()->nullable();
			$table->integer('idAccAccOrigin')->unsigned()->nullable();
			$table->integer('idAccAccDestiny')->unsigned()->nullable();
			$table->foreign('folio')->references('folio')->on('request_models');
			$table->foreign('kind')->references('idrequestkind')->on('request_kinds');
			$table->foreign('idEnterprise')->references('id')->on('enterprises');
			$table->foreign('idDepartment')->references('id')->on('departments');
			$table->foreign('idArea')->references('id')->on('areas');
			$table->foreign('idProject')->references('idproyect')->on('projects');
			$table->foreign('idAccAcc')->references('idAccAcc')->on('accounts');
			$table->foreign('idresourcedetail')->references('idresourcedetail')->on('resource_details');
			$table->foreign('idRefundDetail')->references('idRefundDetail')->on('refund_details');
			$table->foreign('idExpensesDetail')->references('idExpensesDetail')->on('expenses_details');
			$table->foreign('idUser')->references('id')->on('users');
			$table->foreign('idEnterpriseOrigin')->references('id')->on('enterprises');
			$table->foreign('idEnterpriseDestiny')->references('id')->on('enterprises');
			$table->foreign('idAreaOrigin')->references('id')->on('areas');
			$table->foreign('idAreaDestiny')->references('id')->on('areas');
			$table->foreign('idDepartmentOrigin')->references('id')->on('departments');
			$table->foreign('idDepartmentDestiny')->references('id')->on('departments');
			$table->foreign('idProjectOrigin')->references('idproyect')->on('projects');
			$table->foreign('idProjectDestiny')->references('idproyect')->on('projects');
			$table->foreign('idAccAccOrigin')->references('idAccAcc')->on('accounts');
			$table->foreign('idAccAccDestiny')->references('idAccAcc')->on('accounts');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('request_has_reclassifications');
	}
}
