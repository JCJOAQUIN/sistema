<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeColumnDocRequisitionToRequisitionEmployeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('requisition_employees', function (Blueprint $table) 
        {
            $table->string('doc_requisition',250)->change()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('requisition_employees', function (Blueprint $table) 
        {
            $table->integer('doc_requisition')->change()->nullable();
        });
    }
}
