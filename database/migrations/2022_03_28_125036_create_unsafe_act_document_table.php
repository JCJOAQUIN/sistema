<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUnsafeActDocumentTable extends Migration
{
   
    public function up()
    {
        Schema::create('unsafe_act_documents', function(Blueprint $table)
        {
            $table->increments('id');
			$table->string('path',250);
			$table->integer('unsafe_act_id')->unsigned();
			$table->integer('type');
			$table->timestamps();
            $table->foreign('unsafe_act_id')->references('id')->on('unsafe_acts');
        });
    }

    
    public function down()
    {
        Schema::dropIfExists('unsafe_act_documents');
    }
}
