<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateComputerEquipmentsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('computer_equipments', function (Blueprint $table)
		{
			$table->increments('id');
			$table->integer('quantity')->nullable();
			$table->text('type')->nullable();
			$table->text('brand')->nullable();
			$table->text('storage')->nullable();
			$table->text('processor')->nullable();
			$table->text('ram')->nullable();
			$table->text('sku')->nullable();
			$table->decimal('amountUnit',20,2)->nullable();
			$table->text('commentaries')->nullable();
			$table->string('typeTax',50)->nullable();
			$table->decimal('subtotal',20,2)->nullable();
			$table->decimal('iva',20,2)->nullable();
			$table->decimal('amountTotal',20,2)->nullable();
			$table->tinyInteger('status')->default(1);
			$table->integer('idEnterprise')->unsigned()->nullable();
			$table->integer('account')->unsigned()->nullable();
			$table->integer('place_location')->unsigned()->nullable();
			$table->integer('idElaborate')->unsigned()->nullable();
			$table->date('date')->nullable();
			$table->foreign('idEnterprise')->references('id')->on('enterprises');
			$table->foreign('account')->references('idAccAcc')->on('accounts');
			$table->foreign('place_location')->references('id')->on('places');
			$table->foreign('idElaborate')->references('id')->on('users');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('computer_equipments');
	}
}
