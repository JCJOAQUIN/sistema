<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsToAuditsTable extends Migration
{
	public function up()
	{
		Schema::table('audits', function (Blueprint $table) 
		{
			$table->integer('people_involved')->nullable();
			$table->integer('cat_auditor_id')->unsigned()->nullable();
			$table->string('pti_responsible',500);
			$table->text('observations')->nullable();

			$table->foreign('cat_auditor_id')->references('id')->on('cat_auditors');
		});
	}

	public function down()
	{
		Schema::table('audits', function (Blueprint $table) 
		{
			$table->dropForeign(['cat_auditor_id']);
			
			$table->dropColumn('observations');
			$table->dropColumn('pti_responsible');
			$table->dropColumn('cat_auditor_id');
			$table->dropColumn('people_involved');
		});
	}
}
