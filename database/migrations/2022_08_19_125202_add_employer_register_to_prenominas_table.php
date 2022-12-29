<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEmployerRegisterToPrenominasTable extends Migration
{
	public function up()
	{
		Schema::table('prenominas', function(Blueprint $table) 
		{
			$table->string('employer_register',100)->nullable();
		});
	}

	public function down()
	{
		Schema::table('prenominas', function(Blueprint $table) 
		{
			$table->dropColumn('employer_register');
		});
	}
}
