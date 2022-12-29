<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAuditSubcategoriesTable extends Migration
{
    public function up()
    {
        Schema::create('audit_subcategories', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name',2500);
            $table->integer('audit_category_id')->unsigned();
            $table->timestamps();
            $table->foreign('audit_category_id')->references('id')->on('audit_categories');
        });
    }

    public function down()
    {
        Schema::dropIfExists('audit_subcategories');
    }
}
