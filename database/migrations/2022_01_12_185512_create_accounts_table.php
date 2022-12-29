<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAccountsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('accounts', function (Blueprint $table)
		{
			$table->increments('idAccAcc');
			$table->string('account',50);
			$table->text('description');
			$table->decimal('balance',16,2);
			$table->tinyInteger('selectable')->default(1);
			$table->text('content')->nullable();
			$table->integer('idEnterprise')->unsigned();
			$table->integer('identifier');
			$table->timestamps();
			$table->foreign('idEnterprise')->references('id')->on('enterprises');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('accounts');
	}
}
