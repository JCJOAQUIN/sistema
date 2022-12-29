<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePreventiveRiskInspectionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('preventive_risk_inspection',function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('project_id')->unsigned();
            $table->integer('wbs_id')->unsigned()->nullable();
            $table->integer('contractor_id')->unsigned()->nullable();
            $table->string('area',150);
            $table->date('date');
            $table->integer('heading')->nullable();
            $table->string('supervisor_name',150)->nullable();
            $table->string('responsible_name',150)->nullable();
            $table->text('observation',500)->nullable();
            $table->integer('user_id')->unsigned();
            $table->timestamps();
            $table->foreign('project_id')->references('idproyect')->on('projects');
            $table->foreign('wbs_id')->references('id')->on('cat_code_w_bs');
            $table->foreign('contractor_id')->references('id')->on('contractors');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
       Schema::dropIfExists('preventive_risk_inspection');
    }
}
