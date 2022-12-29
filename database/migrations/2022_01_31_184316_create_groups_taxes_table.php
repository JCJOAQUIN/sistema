<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGroupsTaxesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('groups_taxes', function (Blueprint $table)
		{
			$table->increments('idgroupsTaxes');
			$table->string('name',500)->nullable();
			$table->decimal('amount',20,2);
			$table->integer('idgroupsDetail')->unsigned();
			$table->foreign('idgroupsDetail')->references('idgroupsDetail')->on('groups_details');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('groups_taxes');
	}
}
