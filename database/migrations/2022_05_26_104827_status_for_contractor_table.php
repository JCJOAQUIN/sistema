<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class StatusForContractorTable extends Migration
{
	public function up()
	{
		Schema::table('contractors', function (Blueprint $table)
		{
			$table->tinyInteger('status')->nullable();
		});
	}

	public function down()
	{
		Schema::table('contractors', function (Blueprint $table)
		{
			$table->dropColumn('status');
		});
	}
}
