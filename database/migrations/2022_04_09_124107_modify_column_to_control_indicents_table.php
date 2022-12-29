<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyColumnToControlIndicentsTable extends Migration
{
    public function up()
    {
        Schema::table('control_incidents', function (Blueprint $table) 
        {
            $table->dropForeign(['real_employee_id']);
        });
    }

    public function down()
    {
        Schema::table('control_incidents', function (Blueprint $table) 
        {
            $table->foreign('real_employee_id')->references('id')->on('real_employees');
        });
    }
}
