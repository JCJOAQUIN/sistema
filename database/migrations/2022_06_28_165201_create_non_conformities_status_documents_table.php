<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNonConformitiesStatusDocumentsTable extends Migration
{
    public function up()
    {
        Schema::create('non_conformities_status_documents', function (Blueprint $table) {
            $table->increments('id');
            $table->string('path');
            $table->integer('user_id')->unsigned();
            $table->integer('non_conformities_status_id')->unsigned();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('non_conformities_status_documents');
    }
}
