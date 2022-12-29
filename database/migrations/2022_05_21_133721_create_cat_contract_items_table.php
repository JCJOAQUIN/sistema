<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCatContractItemsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('cat_contract_items', function (Blueprint $table)
		{
			$table->increments('id');
			$table->string('contract_item');
			$table->string('activity');
			$table->string('unit')->nullable();
			$table->decimal('pu',20,2)->nullable();
			$table->decimal('amount',20,2)->nullable();
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('cat_contract_items');
	}
}
