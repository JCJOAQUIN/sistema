<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserHasEnterpriseTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('user_has_enterprise', function (Blueprint $table)
		{
			$table->integer('enterprise_id')->unsigned();
			$table->integer('user_id')->unsigned();
			$table->foreign('enterprise_id')->references('id')->on('enterprises');
			$table->foreign('user_id')->references('id')->on('users');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('user_has_enterprise');
	}
}
