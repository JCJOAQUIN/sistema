<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAuditsTable extends Migration
{
    public function up()
    {
        Schema::create('audits', function (Blueprint $table) 
        {
            $table->increments('id');
            $table->integer('project_id')->unsigned();
            $table->integer('wbs_id')->unsigned()->nullable();
            $table->integer('contractor_id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->string('contract',500);
            $table->integer('type_audit');
            $table->date('date');
            $table->string('auditor',500);
            $table->timestamps();
            $table->foreign('project_id')->references('idproyect')->on('projects');
            $table->foreign('wbs_id')->references('id')->on('cat_code_w_bs');
            $table->foreign('contractor_id')->references('id')->on('contractors');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    public function down()
    {
        Schema::dropIfExists('audits');
    }
}
