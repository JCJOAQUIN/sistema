<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnOtherRetentionToNominaEmployeesTable extends Migration
{
    public function up()
    {
        Schema::table('nomina_employees', function(Blueprint $table) 
        {
            $table->decimal('other_retention',16,2)->nullable();
        });
    }

    public function down()
    {
        Schema::table('nomina_employees', function(Blueprint $table) 
        {
            $table->dropColumn('other_retention');
        });
    }
}
