<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUpdatedAtToPrenominasTable extends Migration
{
    public function up()
    {
        Schema::table('prenominas', function(Blueprint $table) 
        {
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down()
    {
        Schema::table('prenominas', function(Blueprint $table) 
        {
            $table->dropColumn('updated_at');
        });
    }
}
