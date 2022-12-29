<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRelatedBillsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('related_bills', function (Blueprint $table)
		{
			$table->increments('id');
			$table->integer('idBill')->unsigned();
			$table->integer('idRelated')->unsigned();
			$table->integer('partial')->nullable();
			$table->decimal('prevBalance',16,2)->nullable();
			$table->decimal('amount',16,2)->nullable();
			$table->decimal('unpaidBalance',16,2)->nullable();
			$table->string('cat_tax_object_id',5)->nullable();
			$table->string('cat_relation_id',5)->nullable();
			$table->foreign('idBill')->references('idBill')->on('bills');
			$table->foreign('idRelated')->references('idBill')->on('bills');
			$table->foreign('cat_tax_object_id')->references('id')->on('cat_tax_objects');
			$table->foreign('cat_relation_id')->references('typeRelation')->on('cat_relations');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('related_bills');
	}
}
