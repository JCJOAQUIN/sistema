<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUnsafePracticeDocumentsTable extends Migration
{
	public function up()
	{
		Schema::create('unsafe_practice_documents', function (Blueprint $table) 
		{
			$table->increments('id');
			$table->string('path',250);
			$table->integer('unsafe_practice_id')->unsigned();
			$table->integer('type');
			$table->timestamps();
			$table->foreign('unsafe_practice_id')->references('id')->on('unsafe_practices');
		});
	}

	public function down()
	{
		Schema::dropIfExists('unsafe_practice_documents');
	}
}
