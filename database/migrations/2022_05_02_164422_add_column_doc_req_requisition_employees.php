<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnDocReqRequisitionEmployees extends Migration
{
    public function up()
    {
        Schema::table('requisition_employees', function (Blueprint $table) 
        {
            $table->integer('doc_requisition')->nullable();
        });
    }

    public function down()
    {
        Schema::table('requisition_employees', function (Blueprint $table) 
        {
             $table->dropColumn('doc_requisition');
        });
    }
}
