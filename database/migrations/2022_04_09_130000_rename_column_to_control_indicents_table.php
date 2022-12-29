<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameColumnToControlIndicentsTable extends Migration
{
    public function up()
    {
        Schema::table('control_incidents', function (Blueprint $table) 
        {
            $table->renameColumn('real_employee_id','employee');
            

        });
    }

    public function down()
    {
        Schema::table('control_incidents', function (Blueprint $table) 
        {
            $table->renameColumn('employee','real_employee_id');
        });
    }
}
