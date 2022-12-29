<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAuditHasOtherAuditorsTable extends Migration
{
	public function up()
	{
		Schema::create('audit_has_other_auditors', function (Blueprint $table) 
		{
			$table->increments('id');
			$table->string('name');
			$table->integer('audit_id')->unsigned();
			$table->integer('type');
			$table->timestamps();
			$table->foreign('audit_id')->references('id')->on('audits');
		});
	}

	public function down()
	{
		Schema::dropIfExists('audit_has_other_auditors');
	}
}
