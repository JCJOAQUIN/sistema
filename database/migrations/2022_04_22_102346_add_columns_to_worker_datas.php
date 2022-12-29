<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsToWorkerDatas extends Migration
{
	public function up()
	{
		Schema::table('worker_datas', function (Blueprint $table) 
		{
			$table->string('position_immediate_boss',500)->nullable();
			$table->decimal('viatics',16,2)->nullable();
			$table->decimal('camping',16,2)->nullable();
		});
	}

	public function down()
	{
		Schema::table('worker_datas', function (Blueprint $table) 
		{
			$table->dropColumn('position_immediate_boss');
			$table->dropColumn('viatics');
			$table->dropColumn('camping');
		});
	}
}
