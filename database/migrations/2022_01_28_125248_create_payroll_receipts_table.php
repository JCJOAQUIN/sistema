<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePayrollReceiptsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('payroll_receipts', function (Blueprint $table)
		{
			$table->increments('id');
			$table->integer('idnominaemployeenf')->unsigned()->nullable();
			$table->string('path',255)->nullable();
			$table->timestamp('signed_at')->nullable();
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('payroll_receipts');
	}
}
