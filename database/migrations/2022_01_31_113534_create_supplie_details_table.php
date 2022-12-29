<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSupplieDetailsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('supplie_details', function (Blueprint $table)
		{
			$table->increments('id');
			$table->integer('idUpload')->unsigned();
			$table->text('groupName');
			$table->text('code');
			$table->text('concept');
			$table->string('measurement',200);
			$table->date('date');
			$table->decimal('amount',50,25);
			$table->decimal('price',50,25);
			$table->decimal('import',50,25);
			$table->decimal('incidence',50,25);
			$table->foreign('idUpload')->references('id')->on('supplie_uploads');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('supplie_details');
	}
}
