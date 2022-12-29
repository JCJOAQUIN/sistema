<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCatAuditorsTable extends Migration
{
	public function up()
	{
		Schema::create('cat_auditors', function (Blueprint $table) {
			$table->increments('id');
			$table->string('name');
			$table->timestamps();
		});
	}
	
	public function down()
	{
		Schema::dropIfExists('cat_auditors');
	}
}
