<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBudgetDetailsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('budget_details', function (Blueprint $table)
		{
			$table->increments('id');
			$table->integer('idUpload')->unsigned();
			$table->text('code');
			$table->text('concept');
			$table->string('measurement', 200)->nullable();
			$table->decimal('amount', 50,25)->nullable();
			$table->decimal('price', 50,25)->nullable();
			$table->decimal('import', 50,25)->nullable();
			$table->decimal('incidence', 50,25)->nullable();
			$table->integer('father')->unsigned()->nullable();
			$table->foreign('idUpload')->references('id')->on('budget_uploads');
			$table->foreign('father')->references('id')->on('budget_details');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('budget_details');
	}
}
