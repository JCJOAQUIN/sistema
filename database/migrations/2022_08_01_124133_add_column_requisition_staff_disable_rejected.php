<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnRequisitionStaffDisableRejected extends Migration
{
    public function up()
    {
        Schema::table('request_models', function (Blueprint $table) 
        {
            $table->tinyInteger('disable_rejected')->default(0);
        });
    }

    public function down()
    {
        Schema::table('request_models', function (Blueprint $table) 
        {
             $table->dropColumn('disable_rejected');
        });
    }
}
