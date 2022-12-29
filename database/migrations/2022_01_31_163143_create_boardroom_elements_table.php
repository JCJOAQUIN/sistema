<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBoardroomElementsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('boardroom_elements', function (Blueprint $table)
		{
			$table->increments('id');
			$table->integer('quantity');
			$table->integer('element_id')->unsigned();
			$table->integer('boardroom_id')->unsigned();
			$table->text('description')->nullable();
			$table->timestamps();
			$table->foreign('element_id')->references('id')->on('cat_elements');
			$table->foreign('boardroom_id')->references('id')->on('boardrooms');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('boardroom_elements');
	}
}
