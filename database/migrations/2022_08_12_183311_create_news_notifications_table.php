<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNewsNotificationsTable extends Migration
{
    public function up()
    {
        Schema::create('news_notifications', function (Blueprint $table) 
        {
            $table->increments('id');
            $table->string('description',2500)->nullable();
            $table->tinyInteger('status')->comment('1. Activo, 2. Inactivo');
            $table->integer('user_id')->unsigned();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    public function down()
    {
        Schema::dropIfExists('news_notifications');
    }
}
