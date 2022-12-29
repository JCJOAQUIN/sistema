<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNonConformitiesStatusesTable extends Migration
{
	public function up()
	{
		Schema::create('non_conformities_statuses', function (Blueprint $table) 
		{
			$table->increments('id');
			$table->integer('project_id')->unsigned();
			$table->integer('wbs_id')->unsigned()->nullable();
			$table->text('description');
			$table->date('date');
			$table->string('location',500);
			$table->string('process_area',500);
			$table->string('non_conformity_origin',500);
			$table->integer('type_of_action');
			$table->string('action',500);
			$table->string('emited_by',500);
			$table->integer('status');
			$table->string('nc_report_number',500);
			$table->date('close_date');
			$table->text('observations');
			$table->integer('user_id')->unsigned();
			$table->timestamps();
            $table->foreign('project_id')->references('idproyect')->on('projects');
            $table->foreign('wbs_id')->references('id')->on('cat_code_w_bs');
            $table->foreign('user_id')->references('id')->on('users');

		});
	}

	public function down()
	{
		Schema::dropIfExists('non_conformities_statuses');
	}
}
