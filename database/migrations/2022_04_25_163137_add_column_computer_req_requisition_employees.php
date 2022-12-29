<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnComputerReqRequisitionEmployees extends Migration
{
    public function up()
    {
        Schema::table('requisition_employees', function (Blueprint $table) 
        {
            $table->integer('computer_required')->nullable();
        });
    }

    public function down()
    {
        Schema::table('requisition_employees', function (Blueprint $table) 
        {
             $table->dropColumn('computer_required');
        });
    }
}
