<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnUpdatedAtToTables extends Migration
{
    public function up()
    {
        Schema::table('clients', function(Blueprint $table) 
        {
            $table->timestamp('updated_at');
        });

        Schema::table('providers', function(Blueprint $table) 
        {
            $table->timestamp('updated_at');
        });

        Schema::table('provider_datas', function(Blueprint $table) 
        {
            $table->timestamp('updated_at');
        });

    }

    public function down()
    {
        Schema::table('clients', function(Blueprint $table) 
        {
            $table->dropColumn('updated_at');
        });

        Schema::table('providers', function(Blueprint $table) 
        {
            $table->dropColumn('updated_at');
        });

        Schema::table('provider_datas', function(Blueprint $table) 
        {
            $table->dropColumn('updated_at');
        });
    }
}
