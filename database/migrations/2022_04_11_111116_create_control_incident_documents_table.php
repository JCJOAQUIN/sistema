<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateControlIncidentDocumentsTable extends Migration
{
    public function up()
    {
        Schema::create('control_incident_documents', function (Blueprint $table) 
        {
            $table->increments('id');
            $table->string('path');
            $table->integer('user_id')->unsigned();
            $table->integer('control_incident_id')->unsigned();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('control_incident_documents');
    }
}
