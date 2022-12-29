<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBillNominaReceiversTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('bill_nomina_receivers', function (Blueprint $table)
		{
			$table->increments('id');
			$table->string('curp',20);
			$table->string('contractType_id',5);
			$table->string('regime_id',5);
			$table->integer('employee_id')->unsigned();
			$table->string('periodicity',5);
			$table->string('c_state',4);
			$table->string('nss',15)->nullable();
			$table->date('laboralDateStart');
			$table->string('antiquity',15);
			$table->integer('job_risk')->unsigned();
			$table->decimal('sdi',16,2);
			$table->integer('bill_id')->unsigned();
			$table->foreign('contractType_id')->references('id')->on('cat_contract_types');
			$table->foreign('regime_id')->references('id')->on('cat_regime_types');
			$table->foreign('employee_id')->references('id')->on('real_employees');
			$table->foreign('periodicity')->references('c_periodicity')->on('cat_periodicities');
			$table->foreign('job_risk')->references('id')->on('cat_position_risks');
			$table->foreign('bill_id')->references('idBill')->on('bills');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('bill_nomina_receivers');
	}
}
