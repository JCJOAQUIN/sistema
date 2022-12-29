<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWorkerDatasTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('worker_datas', function (Blueprint $table)
		{
			$table->increments('id');
			$table->integer('idEmployee')->unsigned();
			$table->integer('state')->unsigned()->nullable();
			$table->integer('project')->unsigned()->nullable();
			$table->integer('enterprise')->unsigned()->nullable();
			$table->integer('account')->unsigned()->nullable();
			$table->integer('direction')->unsigned()->nullable();
			$table->integer('department')->unsigned()->nullable();
			$table->text('position')->nullable();
			$table->date('admissionDate')->nullable();
			$table->date('imssDate')->nullable();
			$table->date('downDate')->nullable();
			$table->date('endingDate')->nullable();
			$table->date('reentryDate')->nullable();
			$table->string('workerType',5)->nullable();
			$table->string('regime_id',5)->nullable();
			$table->tinyInteger('workerStatus')->nullable();
			$table->text('status_reason')->nullable();
			$table->decimal('sdi',16,2)->nullable();
			$table->string('periodicity',3)->nullable();
			$table->string('employer_register',100)->nullable();
			$table->integer('paymentWay')->unsigned()->nullable();
			$table->decimal('netIncome',16,2)->nullable();
			$table->decimal('complement',16,2)->nullable();
			$table->decimal('fonacot',16,2)->nullable();
			$table->integer('nomina')->nullable();
			$table->integer('bono')->nullable();
			$table->string('infonavitCredit',100)->nullable();
			$table->decimal('infonavitDiscount',24,6)->nullable();
			$table->tinyInteger('infonavitDiscountType')->nullable();
			$table->tinyInteger('alimonyDiscountType')->nullable();
			$table->decimal('alimonyDiscount',24,6)->nullable();
			$table->integer('enterpriseOld')->unsigned()->nullable();
			$table->date('admissionDateOld')->nullable();
			$table->tinyInteger('visible')->default(1);
			$table->integer('recorder')->unsigned();
			$table->tinyInteger('status_imss')->default(1)->nullable();
			$table->integer('wbs_id')->unsigned()->nullable();
			$table->string('immediate_boss',500)->nullable();
			$table->timestamps();
			$table->foreign('idEmployee')->references('id')->on('real_employees');
			$table->foreign('state')->references('idstate')->on('states');
			$table->foreign('project')->references('idproyect')->on('projects');
			$table->foreign('enterprise')->references('id')->on('enterprises');
			$table->foreign('account')->references('idAccAcc')->on('accounts');
			$table->foreign('direction')->references('id')->on('areas');
			$table->foreign('department')->references('id')->on('departments');
			$table->foreign('workerType')->references('id')->on('cat_contract_types');
			$table->foreign('regime_id')->references('id')->on('cat_regime_types');
			$table->foreign('periodicity')->references('c_periodicity')->on('cat_periodicities');
			$table->foreign('paymentWay')->references('idPaymentMethod')->on('payment_methods');
			$table->foreign('enterpriseOld')->references('id')->on('enterprises');
			$table->foreign('recorder')->references('id')->on('users');
			$table->foreign('wbs_id')->references('id')->on('cat_code_w_bs');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('worker_datas');
	}
}
