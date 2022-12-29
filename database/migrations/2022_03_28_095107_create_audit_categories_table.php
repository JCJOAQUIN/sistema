<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAuditCategoriesTable extends Migration
{
    public function up()
    {
        Schema::create('audit_categories', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name',2500);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('audit_categories');
    }
}
