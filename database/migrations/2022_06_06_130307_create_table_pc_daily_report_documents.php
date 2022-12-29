<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTablePcDailyReportDocuments extends Migration
{
    public function up()
    {
        Schema::create('pc_daily_report_details_documents', function (Blueprint $table)
		{
			$table->increments('id');
			$table->text('path')->nullable();
            $table->string('kind',50)->nullable();
			$table->integer('pcdr_details_id')->unsigned()->nullable();
			$table->foreign('pcdr_details_id')->references('id')->on('pc_daily_report_details');
            $table->timestamps();
		});
    }

    public function down()
    {
        Schema::dropIfExists('pc_daily_report_details_documents');
    }
}
