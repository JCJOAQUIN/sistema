<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTablePcDailyReport extends Migration
{
    public function up()
    {
        Schema::create('pc_daily_report', function (Blueprint $table)
        {
            $table->increments('id');
            $table->integer('user_elaborate_id')->unsigned()->nullable();
            $table->integer('project_id')->unsigned()->nullable();
            $table->integer('contract_id')->unsigned()->nullable();
            $table->date('date')->nullable();
            $table->integer('wbs_id')->unsigned()->nullable();
            $table->integer('weather_conditions_id')->unsigned()->nullable();
            $table->integer('discipline_id')->unsigned()->nullable();
            $table->time('work_hours_from')->nullable();
            $table->time('work_hours_to')->nullable();
            $table->time('tm_internal_hours_from')->nullable();
            $table->time('tm_internal_hours_to')->nullable();
            $table->integer('tm_internal_id')->unsigned()->nullable();
            $table->time('tm_client_hours_from')->nullable();
            $table->time('tm_client_hours_to')->nullable();
            $table->integer('tm_client_id')->unsigned()->nullable();
            $table->string('comments',300)->nullable();
            $table->tinyInteger('status')->nullable()->comment('0. CERRADO 1. ABIERTO 2. ELIMINADO');
            $table->string('project',10)->nullable();
            $table->string('package',50)->nullable();
            $table->string('kind_doc',10)->nullable();
            $table->string('name_file',50)->nullable();
            $table->timestamps();
            $table->foreign('user_elaborate_id')->references('id')->on('users');
            $table->foreign('project_id')->references('idproyect')->on('projects');
            $table->foreign('contract_id')->references('id')->on('contracts');
            $table->foreign('wbs_id')->references('id')->on('cat_code_w_bs');
            $table->foreign('weather_conditions_id')->references('id')->on('cat_weather_conditions');
            $table->foreign('discipline_id')->references('id')->on('cat_disciplines');
            $table->foreign('tm_internal_id')->references('id')->on('cat_t_ms');
            $table->foreign('tm_client_id')->references('id')->on('cat_t_ms');
        });
        
    }

    public function down()
    {  
        Schema::dropIfExists('pc_daily_report');   
    }
}
