<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBreakdownWagesDetailsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('breakdown_wages_details', function (Blueprint $table)
		{
			$table->increments('id');
			$table->integer('idUpload')->unsigned();
			$table->text('groupName');
			$table->text('code');
			$table->text('concept');
			$table->string('measurement',200);
			$table->decimal('baseSalaryPerDay',50,25);
			$table->decimal('realSalaryFactor',50,25);
			$table->decimal('realSalary',50,25);
			$table->decimal('viatics',50,25)->nullable();
			$table->decimal('feeding',50,25)->nullable();
			$table->decimal('totalSalary',50,25);
			$table->timestamps();
			$table->foreign('idUpload')->references('id')->on('breakdown_wages_uploads');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('breakdown_wages_details');
	}
}
