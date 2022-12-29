<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRequestModelsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('request_models', function (Blueprint $table)
		{
			$table->increments('folio');
			$table->integer('kind')->unsigned();
			$table->datetime('fDate')->nullable();
			$table->datetime('reviewDate')->nullable();
			$table->datetime('authorizeDate')->nullable();
			$table->tinyInteger('taxPayment')->default(0)->nullable();
			$table->datetime('PaymentDate')->nullable();
			$table->datetime('deliveryDate')->nullable();
			$table->integer('status')->unsigned()->nullable();
			$table->integer('account')->unsigned()->nullable();
			$table->integer('accountR')->unsigned()->nullable();
			$table->integer('idEnterprise')->unsigned()->nullable();
			$table->integer('idArea')->unsigned()->nullable();
			$table->integer('idDepartment')->unsigned()->nullable();
			$table->integer('idEnterpriseR')->unsigned()->nullable();
			$table->integer('idAreaR')->unsigned()->nullable();
			$table->integer('idDepartamentR')->unsigned()->nullable();
			$table->integer('idAccAcc')->unsigned()->nullable();
			$table->integer('idRequest')->unsigned()->nullable();
			$table->integer('idElaborate')->unsigned()->nullable();
			$table->integer('idCheck')->unsigned()->nullable();
			$table->integer('idAuthorize')->unsigned()->nullable();
			$table->text('checkComment')->nullable();
			$table->text('authorizeComment')->nullable();
			$table->integer('idProject')->unsigned()->nullable();
			$table->integer('idProjectR')->unsigned()->nullable();
			$table->integer('payment')->default(0);
			$table->text('code')->nullable();
			$table->tinyInteger('free')->default(0);
			$table->timestamp('payDate')->nullable();
			$table->text('paymentComment')->nullable();
			$table->integer('idprenomina')->unsigned()->nullable();
			$table->integer('idCheckConstruction')->nullable();
			$table->datetime('reviewDateConstruction')->nullable();
			$table->integer('idWarehouseType')->unsigned()->nullable();
			$table->integer('remittance')->default(0);
			$table->integer('idRequisition')->unsigned()->nullable();
			$table->tinyInteger('statusWarehouse')->default(0);
			$table->string('new_folio',50)->nullable();
			$table->integer('goToWarehouse')->default(1);
			$table->integer('code_edt')->nullable();
			$table->integer('code_wbs')->nullable();
			$table->integer('idCancelled')->unsigned()->nullable();
			$table->foreign('kind')->references('idrequestkind')->on('request_kinds');
			$table->foreign('status')->references('idrequestStatus')->on('status_requests');
			$table->foreign('account')->references('idAccAcc')->on('accounts');
			$table->foreign('accountR')->references('idAccAcc')->on('accounts');
			$table->foreign('idEnterprise')->references('id')->on('enterprises');
			$table->foreign('idArea')->references('id')->on('areas');
			$table->foreign('idDepartment')->references('id')->on('departments');
			$table->foreign('idEnterpriseR')->references('id')->on('enterprises');
			$table->foreign('idAreaR')->references('id')->on('areas');
			$table->foreign('idDepartamentR')->references('id')->on('departments');
			$table->foreign('idAccAcc')->references('idAccAcc')->on('accounts');
			$table->foreign('idRequest')->references('id')->on('users');
			$table->foreign('idElaborate')->references('id')->on('users');
			$table->foreign('idCheck')->references('id')->on('users');
			$table->foreign('idAuthorize')->references('id')->on('users');
			$table->foreign('idProject')->references('idproyect')->on('projects');
			$table->foreign('idProjectR')->references('idproyect')->on('projects');
			$table->foreign('idprenomina')->references('idprenomina')->on('prenominas');
			$table->foreign('idWarehouseType')->references('id')->on('cat_warehouse_types');
			$table->foreign('idRequisition')->references('folio')->on('request_models');
			$table->foreign('idCancelled')->references('id')->on('users');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('request_models');
	}
}
