<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUnsafeConditionsTable extends Migration
{
    public function up()
    {
        Schema::create('unsafe_conditions', function (Blueprint $table) 
        {
            $table->increments('id');
            $table->integer('category_id')->unsigned();
            $table->integer('subcategory_id')->unsigned();
            $table->string('dangerousness',5);
            $table->text('description');
            $table->text('action');
            $table->text('prevent');
            $table->text('re');
            $table->date('fv');
            $table->integer('status');
            $table->string('responsable',500);
            $table->integer('audit_id')->unsigned();;
            $table->timestamps();
            $table->foreign('category_id')->references('id')->on('audit_categories');
            $table->foreign('subcategory_id')->references('id')->on('audit_subcategories');
            $table->foreign('audit_id')->references('id')->on('audits');
        });
    }

    public function down()
    {
        Schema::dropIfExists('unsafe_conditions');
    }
}
