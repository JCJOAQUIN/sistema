<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNominaAppEmpsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('nomina_app_emps', function (Blueprint $table)
		{
			$table->increments('idNomAppEmp');
			$table->integer('idNominaApplication')->unsigned()->nullable();
			$table->string('bank',100)->nullable();
			$table->string('account',45)->nullable();
			$table->string('clabe',45)->nullable();
			$table->string('cardNumber',45)->nullable();
			$table->string('reference',45)->nullable();
			$table->decimal('amount',16,2)->nullable();
			$table->string('description',45)->nullable();
			$table->integer('idUsers')->unsigned()->nullable();
			$table->integer('idpaymentMethod')->unsigned()->nullable();
			$table->integer('idAccount')->unsigned()->nullable();
			$table->integer('idEnterprise')->unsigned()->nullable();
			$table->integer('idArea')->unsigned()->nullable();
			$table->integer('idDepartment')->unsigned()->nullable();
			$table->integer('idProject')->unsigned()->nullable();
			$table->foreign('idNominaApplication')->references('idNominaApplication')->on('nomina_applications');
			$table->foreign('idUsers')->references('id')->on('users');
			$table->foreign('idpaymentMethod')->references('idpaymentMethod')->on('payment_methods');
			$table->foreign('idAccount')->references('idAccAcc')->on('accounts');
			$table->foreign('idEnterprise')->references('id')->on('enterprises');
			$table->foreign('idArea')->references('id')->on('areas');
			$table->foreign('idDepartment')->references('id')->on('departments');
			$table->foreign('idProject')->references('idproyect')->on('projects');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('nomina_app_emps');
	}
}
