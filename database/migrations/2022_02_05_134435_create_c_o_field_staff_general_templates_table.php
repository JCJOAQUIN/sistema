<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCOFieldStaffGeneralTemplatesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('c_o_field_staff_general_templates', function (Blueprint $table)
		{
			$table->increments('id');
			$table->integer('idUpload')->unsigned()->nullable();
			$table->decimal('montototal',24,6)->nullable();
			$table->decimal('porcentaje',24,6)->nullable();
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
		Schema::dropIfExists('c_o_field_staff_general_templates');
	}
}