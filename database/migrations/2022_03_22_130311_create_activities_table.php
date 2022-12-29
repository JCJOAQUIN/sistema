<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateActivitiesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('activities', function (Blueprint $table)
		{
			$table->increments('id');
			$table->integer('project_id')->unsigned();
			$table->integer('wbs_id')->unsigned()->nullable();
			$table->string('folio',45);
			$table->string('contractor',150);
			$table->string('specialty',150);
			$table->date('start_date');
			$table->time('start_hour');
			$table->date('end_date');
			$table->time('end_hour');
			$table->string('area',45);
			$table->string('personal_number',20);
			$table->string('resource_code',10);
			$table->string('status_code',10);
			$table->string('causes_code',10);
			$table->text('description');
			$table->integer('user_id')->unsigned();
			$table->foreign('project_id')->references('idproyect')->on('projects');
			$table->foreign('wbs_id')->references('id')->on('cat_code_w_bs');
			$table->foreign('user_id')->references('id')->on('users');
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
		Schema::dropIfExists('activities');
	}
}
