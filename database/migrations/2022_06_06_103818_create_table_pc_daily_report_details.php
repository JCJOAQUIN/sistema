<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTablePcDailyReportDetails extends Migration
{
    public function up()
    {
        Schema::create('pc_daily_report_details', function (Blueprint $table)
        {
            $table->increments('id');
            $table->integer('contract_item_id')->unsigned()->nullable();
            $table->decimal('quantity',20,2)->nullable();
            $table->decimal('amount',20,2)->nullable();
            $table->integer('contractor_id')->unsigned()->nullable();
            $table->string('area',100)->nullable();
            $table->string('place_area',100)->nullable();
            $table->string('num_ppt',100)->nullable();
            $table->integer('blueprint_id')->unsigned()->nullable();
            $table->string('comments',200)->nullable();
            $table->decimal('accumulated',20,2)->nullable();
            $table->integer('pc_daily_report_id')->unsigned()->nullable();
            $table->foreign('contract_item_id')->references('id')->on('cat_contract_items');
            $table->foreign('contractor_id')->references('id')->on('contractors');
            $table->foreign('blueprint_id')->references('id')->on('blueprints');
            $table->foreign('pc_daily_report_id')->references('id')->on('pc_daily_report');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pc_daily_report_details');
    }
}
