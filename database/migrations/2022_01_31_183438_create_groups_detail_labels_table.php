<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGroupsDetailLabelsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('groups_detail_labels', function (Blueprint $table)
		{
			$table->increments('idgroupsDetailLabel');
			$table->integer('idlabels')->unsigned();
			$table->integer('idgroupsDetail')->unsigned();
			$table->foreign('idlabels')->references('idlabels')->on('labels');
			$table->foreign('idgroupsDetail')->references('idgroupsDetail')->on('groups_details');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('groups_detail_labels');
	}
}
