<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddQualifiedEmployeeColumnToRequisitionEmployeesTable extends Migration
{
    public function up()
    {
        Schema::table('requisition_employees', function(Blueprint $table) 
        {
            $table->tinyInteger('qualified_employee')->nullable();
        });
    }

    public function down()
    {
        Schema::table('requisition_employees', function(Blueprint $table) 
        {
            $table->dropColumn('qualified_employee');
        });
    }
}
