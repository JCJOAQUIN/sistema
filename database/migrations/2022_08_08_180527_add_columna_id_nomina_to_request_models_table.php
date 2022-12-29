<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnaIdNominaToRequestModelsTable extends Migration
{
	public function up()
	{
		Schema::table('request_models', function(Blueprint $table) 
		{
			$table->integer('idNomina')->unsigned()->nullable();
			$table->foreign('idNomina')->references('folio')->on('request_models');
		});
	}

	public function down()
	{
		Schema::table('request_models', function(Blueprint $table) 
		{
			$table->dropForeign('idNomina');
			$table->dropColumn('idNomina');
		});
	}
}
