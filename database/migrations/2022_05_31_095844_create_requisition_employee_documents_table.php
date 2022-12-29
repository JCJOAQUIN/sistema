<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRequisitionEmployeeDocumentsTable extends Migration
{
    public function up()
    {
        Schema::create('requisition_employee_documents', function (Blueprint $table) 
        {
            $table->increments('id');
            $table->string('name',200);
            $table->string('path',200);
            $table->integer('requisition_employee_id');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('requisition_employee_documents');
    }
}
