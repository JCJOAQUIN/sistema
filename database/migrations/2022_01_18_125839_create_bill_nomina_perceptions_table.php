<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBillNominaPerceptionsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('bill_nomina_perceptions', function (Blueprint $table)
		{
			$table->increments('id');
			$table->string('type',5);
			$table->string('perceptionKey',5);
			$table->text('concept');
			$table->decimal('taxedAmount',16,2);
			$table->decimal('exemptAmount',16,2);
			$table->integer('bill_nomina_id')->unsigned();
			$table->foreign('type')->references('id')->on('cat_perceptions');
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
		Schema::dropIfExists('bill_nomina_perceptions');
	}
}
