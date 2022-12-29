<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEstimateNumberColumnToRequestModelsTable extends Migration
{
	public function up()
	{
		Schema::table('request_models', function(Blueprint $table) 
		{
			$table->string('estimate_number')->nullable();
		});
	}

	public function down()
	{
		Schema::table('request_models', function(Blueprint $table) 
		{
			$table->dropColumn('estimate_number');
		});
	}
}
