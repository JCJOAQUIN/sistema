<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePreventiveRiskInspectionDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('preventive_risk_inspection_detail', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('preventive_risk_inspection_id')->unsigned();
            $table->foreign('preventive_risk_inspection_id','preventive_risk_id')->references('id')->on('preventive_risk_inspection');
            $table->integer('category_id')->unsigned();
            $table->foreign('category_id')->references('id')->on('audit_categories');
            $table->integer('subcategory_id')->unsigned()->nullable();
            $table->foreign('subcategory_id')->references('id')->on('audit_subcategories');
            $table->integer('act')->nullable();
            $table->string('severity',5)->nullable();
            $table->time('hour')->nullable();
            $table->string('discipline',100)->nullable();
            $table->text('condition');
            $table->text('action')->nullable();
            $table->string('observer',150);
            $table->string('responsible',150)->nullable();
            $table->integer('status');
            $table->date('dateend')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('preventive_risk_inspection_detail');
    }
}
