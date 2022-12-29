<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBillNominaIndemnificationsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('bill_nomina_indemnifications', function (Blueprint $table)
		{
			$table->increments('id');
			$table->decimal('total_paid',16,2);
			$table->integer('service_year');
			$table->decimal('last_ordinary_monthly_salary',16,2);
			$table->decimal('cumulative_income',16,2);
			$table->decimal('non_cumulative_income',16,2);
			$table->integer('bill_nomina_id')->unsigned();
			$table->foreign('bill_nomina_id')->references('id')->on('bill_nominas');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('bill_nomina_indemnifications');
	}
}
