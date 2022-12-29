<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyColumnEmployeeToControlIndicentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('control_incidents', function (Blueprint $table) 
        {
            $table->string('employee', 2500)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('control_incidents', function (Blueprint $table) 
        {
            $table->integer('employee')->unsigned()->change();
        });
    }
}
