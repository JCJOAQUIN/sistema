<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCatCodeEDTsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('cat_code_e_d_ts', function (Blueprint $table)
		{
			$table->increments('id');
			$table->string('edt_number',3);
			$table->text('code')->nullable();
			$table->text('description')->nullable();
			$table->string('phase',3);
			$table->integer('codewbs_id')->unsigned();
			$table->foreign('codewbs_id')->references('id')->on('cat_code_w_bs');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('cat_code_e_d_ts');
	}
}
