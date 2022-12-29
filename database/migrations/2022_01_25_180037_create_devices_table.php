<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDevicesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('devices', function (Blueprint $table)
		{
			$table->increments('iddevices');
			$table->string('brand',45)->nullable();
			$table->string('type',45);
			$table->string('characteristics',500)->nullable();
			$table->decimal('cost',20,2);
			$table->date('buyDate');
			$table->tinyInteger('assign')->default(0);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('devices');
	}
}
