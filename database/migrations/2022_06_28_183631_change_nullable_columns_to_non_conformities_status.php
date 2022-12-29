<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeNullableColumnsToNonConformitiesStatus extends Migration
{
    public function up()
    {
        Schema::table('non_conformities_statuses', function(Blueprint $table) 
        {
            $table->string('action',500)->nullable()->change();
            $table->date('close_date')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('non_conformities_statuses', function(Blueprint $table) 
        {
            $table->string('action',500)->change();
            $table->date('close_date')->change();
        });
    }
}
