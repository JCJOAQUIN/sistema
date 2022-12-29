<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnPhoneToTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('staff_employees', function(Blueprint $table) 
		{
            $table->string('phone',45)->nullable();
		});
        Schema::table('requisition_employees', function(Blueprint $table) 
		{
            $table->string('phone',45)->nullable();
		});
        Schema::table('real_employees', function(Blueprint $table) 
		{
            $table->string('phone',45)->nullable();
		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('staff_employees', function(Blueprint $table) 
		{
            $table->dropColumn('phone');
		});
        Schema::table('requisition_employees', function(Blueprint $table) 
		{
            $table->dropColumn('phone');
		});
        Schema::table('real_employees', function(Blueprint $table) 
		{
            $table->dropColumn('phone');
		});
    }
}
