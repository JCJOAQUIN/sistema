<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsToNominaEmployeeNFTable extends Migration
{
    public function up()
    {
        Schema::table('nomina_employee_n_fs', function(Blueprint $table) 
        {
            $table->decimal('complement_infonavit',20,6)->nullable();
            $table->decimal('extra_time',20,6)->nullable();
            $table->decimal('holiday',20,6)->nullable();
            $table->decimal('sundays',20,6)->nullable();
        });
    }

    public function down()
    {
        Schema::table('nomina_employee_n_fs', function(Blueprint $table) 
        {
            $table->dropColumn('complement_infonavit');
            $table->dropColumn('extra_time');
            $table->dropColumn('holiday');
            $table->dropColumn('sundays');
        });
    }
}
