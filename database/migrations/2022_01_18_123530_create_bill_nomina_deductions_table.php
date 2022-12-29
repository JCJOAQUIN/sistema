<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBillNominaDeductionsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('bill_nomina_deductions', function (Blueprint $table)
		{
			$table->increments('id');
			$table->string('type',5);
			$table->string('deductionKey',5);
			$table->text('concept');
			$table->decimal('amount',16,2);
			$table->integer('bill_nomina_id')->unsigned();
			$table->foreign('type')->references('id')->on('cat_deductions');
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
		Schema::dropIfExists('bill_nomina_deductions');
	}
}
