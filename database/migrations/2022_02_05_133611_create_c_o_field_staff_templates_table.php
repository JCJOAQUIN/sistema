<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCOFieldStaffTemplatesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('c_o_field_staff_templates', function (Blueprint $table)
		{
			$table->increments('id');
			$table->integer('idUpload')->unsigned()->nullable();
			$table->string('group',100)->nullable();
			$table->integer('groupId')->nullable();
			$table->string('category',100)->nullable();
			$table->decimal('amount',24,6)->nullable();
			$table->decimal('salary',24,6)->nullable();
			$table->decimal('import',24,6)->nullable();
			$table->decimal('factor1',24,6)->nullable();
			$table->decimal('factor2',24,6)->nullable();
			$table->foreign('idUpload')->references('id')->on('cost_overruns');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('c_o_field_staff_templates');
	}
}
