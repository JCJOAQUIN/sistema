<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsToPrenominaEmployee extends Migration
{
	public function up()
	{
		Schema::table('prenomina_employee', function(Blueprint $table) 
		{
			$table->integer('absence')->nullable();
			$table->integer('extra_hours')->nullable();
			$table->integer('holidays')->nullable();
			$table->integer('sundays')->nullable();
		});
	}

	public function down()
	{
		Schema::table('prenomina_employee', function(Blueprint $table) 
        {
            $table->dropColumn('absence');
            $table->dropColumn('extra_hours');
            $table->dropColumn('holidays');
            $table->dropColumn('sundays');
        });
	}
}
