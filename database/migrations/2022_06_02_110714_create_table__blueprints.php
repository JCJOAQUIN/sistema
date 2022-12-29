<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableBlueprints extends Migration
{
    public function up()
    {
        Schema::create('blueprints', function (Blueprint $table)
		{
			$table->increments('id');
			$table->string('name');
            $table->integer('wbs_id')->unsigned();
            $table->timestamps();
            $table->foreign('wbs_id')->references('id')->on('cat_code_w_bs');
		});
    }

    public function down()
    {
        Schema::dropIfExists('blueprints');
    }
}