<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateComputersTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('computers', function (Blueprint $table)
		{
			$table->increments('idComputer');
			$table->integer('idFolio')->unsigned();
			$table->integer('idKind')->unsigned();
			$table->integer('role_id')->unsigned()->nullable();
			$table->text('title')->nullable();
			$table->date('datetitle')->nullable();
			$table->tinyInteger('entry')->nullable();
			$table->date('entry_date')->nullable();
			$table->tinyInteger('device')->comment('1. Smartphone, 2. Tablet, 3. Laptop, 4. Computer');
			$table->string('kind_account',100)->nullable();
			$table->string('email_account',500)->nullable();
			$table->string('alias_account',500)->nullable();
			$table->string('position',500);
			$table->text('other_software')->nullable();
			$table->decimal('iva',20,2)->nullable();
			$table->decimal('subtotal',20,2)->nullable();
			$table->decimal('total',20,2)->nullable();
			$table->integer('idComputerEquipment')->unsigned()->nullable();
			$table->integer('idDetailPurchase')->unsigned()->nullable();
			$table->foreign('idFolio')->references('folio')->on('request_models');
			$table->foreign('idKind')->references('idrequestkind')->on('request_kinds');
			$table->foreign('role_id')->references('id')->on('roles');
			$table->foreign('idComputerEquipment')->references('id')->on('computer_equipments');
			$table->foreign('idDetailPurchase')->references('idDetailPurchase')->on('detail_purchases');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('computers');
	}
}
