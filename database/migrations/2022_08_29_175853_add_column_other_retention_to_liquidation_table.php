<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnOtherRetentionToLiquidationTable extends Migration
{
	public function up()
	{
		Schema::table('liquidations', function(Blueprint $table) 
		{
		    $table->decimal('other_retention',20,6)->nullable();
		});
	}

	public function down()
	{
		Schema::table('liquidations', function(Blueprint $table) 
		{
		    $table->dropColumn('other_retention');
		});
	}
}
