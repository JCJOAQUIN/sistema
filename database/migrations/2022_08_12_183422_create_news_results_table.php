<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNewsResultsTable extends Migration
{
	public function up()
	{
		Schema::create('news_results', function (Blueprint $table) 
		{
			$table->increments('id');
			$table->text('url');
			$table->timestamps();
		});
	}

	public function down()
	{
		Schema::dropIfExists('news_results');
	}
}
