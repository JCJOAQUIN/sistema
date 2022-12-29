<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGroupsDetailsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('groups_details', function (Blueprint $table)
		{
			$table->increments('idgroupsDetail');
			$table->decimal('quantity',20,2)->nullable();
			$table->text('unit')->nullable();
			$table->text('description')->nullable();
			$table->decimal('unitPrice',20,2)->nullable();
			$table->decimal('tax',20,2)->nullable();
			$table->decimal('discount',20,2)->nullable();
			$table->decimal('amount',20,2)->nullable();
			$table->string('typeTax',100)->nullable();
			$table->decimal('subtotal',20,2)->nullable();
			$table->integer('idgroups')->unsigned();
			$table->foreign('idgroups')->references('idgroups')->on('groups');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('groups_details');
	}
}
