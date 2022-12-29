<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRealEmployeesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('real_employees', function (Blueprint $table)
		{
			$table->increments('id');
			$table->string('name',500)->nullable();
			$table->string('last_name',500)->nullable();
			$table->string('scnd_last_name',500)->nullable();
			$table->string('curp',20)->nullable();
			$table->string('rfc',15)->nullable();
			$table->string('tax_regime',5)->nullable();
			$table->string('imss',15)->nullable();
			$table->text('street')->nullable();
			$table->string('number',500)->nullable();
			$table->text('colony')->nullable();
			$table->string('cp',20)->nullable();
			$table->text('city')->nullable();
			$table->decimal('workedDays',10,1)->nullable();
			$table->decimal('salaryFiscal',20,2)->nullable();
			$table->decimal('salaryNoFiscal',20,2)->nullable();
			$table->integer('state_id')->unsigned()->nullable();
			$table->integer('sys_user')->nullable();
			$table->string('email',500)->nullable();
			$table->timestamps();
			$table->foreign('state_id')->references('idstate')->on('states');
			$table->foreign('tax_regime')->references('taxRegime')->on('cat_tax_regimes');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('real_employees');
	}
}
