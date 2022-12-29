<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGroupsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('groups', function (Blueprint $table)
		{
			$table->increments('idgroups');
			$table->text('title')->nullable();
			$table->date('datetitle')->nullable();
			$table->string('numberOrder',45)->nullable();
			$table->string('operationType',45)->nullable();
			$table->decimal('amountMovement',20,2)->nullable();
			$table->decimal('amountRetake',20,2)->nullable();
			$table->decimal('commission',20,2)->nullable();
			$table->string('reference',500)->nullable();
			$table->string('typeCurrency',500)->nullable();
			$table->date('paymentDate')->nullable();
			$table->integer('idpaymentMethod')->unsigned()->nullable();
			$table->string('statusBill',100)->nullable();
			$table->decimal('tax',20,2)->nullable();
			$table->decimal('subtotales',20,2)->nullable();
			$table->decimal('amount',20,2)->nullable();
			$table->integer('idFolio')->unsigned()->nullable();
			$table->integer('idKind')->unsigned()->nullable();
			$table->integer('idEnterpriseOrigin')->unsigned()->nullable();
			$table->integer('idDepartamentOrigin')->unsigned()->nullable();
			$table->integer('idAreaOrigin')->unsigned()->nullable();
			$table->integer('idProjectOrigin')->unsigned()->nullable();
			$table->integer('idAccAccOrigin')->unsigned()->nullable();
			$table->integer('idEnterpriseDestiny')->unsigned()->nullable();
			$table->integer('idAccAccDestiny')->unsigned()->nullable();
			$table->integer('idProvider')->unsigned()->nullable();
			$table->integer('provider_data_id')->unsigned()->nullable();
			$table->integer('provider_has_banks_id')->unsigned()->nullable();
			$table->integer('idEnterpriseOriginR')->unsigned()->nullable();
			$table->integer('idAreaOriginR')->unsigned()->nullable();
			$table->integer('idDepartamentOriginR')->unsigned()->nullable();
			$table->integer('idAccAccOriginR')->unsigned()->nullable();
			$table->integer('idProjectOriginR')->unsigned()->nullable();
			$table->integer('idEnterpriseDestinyR')->unsigned()->nullable();
			$table->integer('idAccAccDestinyR')->unsigned()->nullable();
			$table->foreign('idFolio')->references('folio')->on('request_models');
			$table->foreign('idKind')->references('idrequestkind')->on('request_kinds');
			$table->foreign('idEnterpriseOrigin')->references('id')->on('enterprises');
			$table->foreign('idDepartamentOrigin')->references('id')->on('departments');
			$table->foreign('idAreaOrigin')->references('id')->on('areas');
			$table->foreign('idProjectOrigin')->references('idproyect')->on('projects');
			$table->foreign('idAccAccOrigin')->references('idAccAcc')->on('accounts');
			$table->foreign('idEnterpriseDestiny')->references('id')->on('enterprises');
			$table->foreign('idAccAccDestiny')->references('idAccAcc')->on('accounts');
			$table->foreign('idProvider')->references('idProvider')->on('providers');
			$table->foreign('provider_data_id')->references('id')->on('provider_datas');
			$table->foreign('provider_has_banks_id')->references('id')->on('provider_banks');
			$table->foreign('idEnterpriseOriginR')->references('id')->on('enterprises');
			$table->foreign('idAreaOriginR')->references('id')->on('areas');
			$table->foreign('idDepartamentOriginR')->references('id')->on('departments');
			$table->foreign('idAccAccOriginR')->references('idAccAcc')->on('accounts');
			$table->foreign('idProjectOriginR')->references('idproyect')->on('projects');
			$table->foreign('idEnterpriseDestinyR')->references('id')->on('enterprises');
			$table->foreign('idAccAccDestinyR')->references('idAccAcc')->on('accounts');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('groups');
	}
}
