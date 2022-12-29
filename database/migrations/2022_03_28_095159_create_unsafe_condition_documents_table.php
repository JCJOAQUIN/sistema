<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUnsafeConditionDocumentsTable extends Migration
{
	public function up()
	{
		Schema::create('unsafe_condition_documents', function (Blueprint $table) 
		{
			$table->increments('id');
			$table->string('path',250);
			$table->integer('unsafe_condition_id')->unsigned();
			$table->integer('type');
			$table->timestamps();
            $table->foreign('unsafe_condition_id')->references('id')->on('unsafe_conditions');

		});
	}

	public function down()
	{
		Schema::dropIfExists('unsafe_condition_documents');
	}
}
