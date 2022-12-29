<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTablePcDailyReportSignatures extends Migration
{
    public function up()
    {
        Schema::create('pc_daily_report_signatures', function (Blueprint $table)
        {
            $table->increments('id');
            $table->string('name',100)->nullable();
            $table->string('position',100)->nullable();
            $table->integer('pc_daily_report_id')->unsigned()->nullable();
            $table->foreign('pc_daily_report_id')->references('id')->on('pc_daily_report');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pc_daily_report_signatures');
    }
}
