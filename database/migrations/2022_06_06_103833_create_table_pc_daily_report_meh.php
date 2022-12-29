<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTablePcDailyReportMeh extends Migration
{
    public function up()
    {
        Schema::create('pc_daily_report_meh', function (Blueprint $table)
        {
            $table->increments('id');
            $table->integer('quantity')->nullable();
            $table->integer('machinery_id')->unsigned()->nullable();
            $table->foreign('pc_daily_report_id')->references('id')->on('pc_daily_report');
            $table->foreign('machinery_id')->references('id')->on('cat_machinery');
            $table->integer('pc_daily_report_id')->unsigned()->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pc_daily_report_meh');
    }
}
