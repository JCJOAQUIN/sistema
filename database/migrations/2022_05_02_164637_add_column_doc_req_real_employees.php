<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnDocReqRealEmployees extends Migration
{
    public function up()
    {
        Schema::table('real_employees', function (Blueprint $table) 
        {
            $table->string('doc_requisition',250)->nullable();
        });
    }

    public function down()
    {
        Schema::table('real_employees', function (Blueprint $table) 
        {
            $table->dropColumn('doc_requisition');
        });
    }
}
