<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCOTechnicalStaffConceptsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('c_o_technical_staff_concepts', function (Blueprint $table)
		{
			$table->increments('id');
			$table->integer('idUpload')->unsigned()->nullable();
			$table->integer('parent')->unsigned()->nullable();
			$table->text('category')->nullable();
			$table->text('measurement')->nullable();
			$table->decimal('total',24,6)->nullable();
			$table->foreign('idUpload')->references('id')->on('cost_overruns');
			$table->foreign('parent')->references('id')->on('c_o_technical_staff_concepts');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('c_o_technical_staff_concepts');
	}
}
