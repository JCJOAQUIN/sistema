<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCatCodeWBSTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('cat_code_w_bs', function (Blueprint $table)
		{
			$table->increments('id');
			$table->string('code',5);
			$table->text('code_wbs');
			$table->integer('project_id')->unsigned();
			$table->tinyInteger('status')->default(1);
			$table->foreign('project_id')->references('idproyect')->on('projects');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('cat_code_w_bs');
	}
}
