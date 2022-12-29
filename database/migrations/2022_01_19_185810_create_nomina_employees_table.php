<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNominaEmployeesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('nomina_employees', function (Blueprint $table)
		{
			$table->increments('idnominaEmployee');
			$table->integer('idnomina')->unsigned()->nullable();
			$table->integer('idrealEmployee')->unsigned()->nullable();
			$table->integer('idworkingData')->unsigned()->nullable();
			$table->string('type',45)->nullable();
			$table->string('fiscal',45)->nullable();
			$table->tinyInteger('visible')->default(1);
			$table->date('from_date')->nullable();
			$table->date('to_date')->nullable();
			$table->string('idCatPeriodicity',5)->nullable();
			$table->integer('absence')->nullable();
			$table->integer('extra_hours')->nullable();
			$table->integer('holidays')->nullable();
			$table->integer('sundays')->nullable();
			$table->decimal('loan_retention',16,2)->nullable();
			$table->decimal('loan_perception',16,2)->nullable();
			$table->integer('day_bonus')->nullable();
			$table->integer('worked_days')->nullable();
			$table->date('down_date')->nullable();
			$table->decimal('other_perception',16,2)->nullable();
			$table->integer('idpaymentMethod')->unsigned()->nullable();
			$table->tinyInteger('payment')->default(0);
			$table->decimal('total',16,2)->nullable();
			$table->foreign('idnomina')->references('idnomina')->on('nominas');
			$table->foreign('idrealEmployee')->references('id')->on('real_employees');
			$table->foreign('idworkingData')->references('id')->on('worker_datas');
			$table->foreign('idCatPeriodicity')->references('c_periodicity')->on('cat_periodicities');
			$table->foreign('idpaymentMethod')->references('idpaymentMethod')->on('payment_methods');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('nomina_employees');
	}
}
