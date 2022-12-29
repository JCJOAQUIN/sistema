<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTablePcDailyReportStaff extends Migration
{
    public function up()
    {
        Schema::create('pc_daily_report_staff', function (Blueprint $table)
        {
            $table->increments('id');
            $table->integer('quantity')->nullable();
            $table->integer('industrial_staff_id')->unsigned()->nullable();
            $table->integer('hours')->nullable();
            $table->integer('pc_daily_report_id')->unsigned()->nullable();
            $table->foreign('industrial_staff_id')->references('id')->on('cat_industrial_staff');
            $table->foreign('pc_daily_report_id')->references('id')->on('pc_daily_report');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pc_daily_report_staff');
    }
}
