<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGroupingAccountsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('grouping_accounts', function (Blueprint $table)
		{
			$table->increments('id');
			$table->text('name')->nullable();
			$table->integer('idEnterprise')->unsigned();
			$table->integer('idUser')->unsigned();
			$table->foreign('idEnterprise')->references('id')->on('enterprises');
			$table->foreign('idUser')->references('id')->on('users');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('grouping_accounts');
	}
}
