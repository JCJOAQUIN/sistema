<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMobileSessionsTable extends Migration
{
	public function up()
	{
		Schema::create('mobile_sessions', function (Blueprint $table)
		{
			$table->increments('id');
			$table->integer('user_id');
			$table->string('user_kind');
			$table->string('token');
			$table->timestamps();
		});
	}

	public function down()
	{
		Schema::dropIfExists('mobile_sessions');
	}
}
