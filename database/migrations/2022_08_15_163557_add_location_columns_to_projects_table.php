<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLocationColumnsToProjectsTable extends Migration
{
	public function up()
	{
		Schema::table('projects', function(Blueprint $table)
		{
			$table->decimal('latitude', 10, 8)->nullable();
			$table->decimal('longitude', 11, 8)->nullable();
			$table->integer('distance')->nullable();
		});
	}

	public function down()
	{
		Schema::table('projects', function(Blueprint $table)
		{
			$table->dropColumn('latitude');
			$table->dropColumn('longitude');
			$table->dropColumn('distance');
		});
	}
}
