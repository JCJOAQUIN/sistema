<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCatTypeBillsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('cat_type_bills', function (Blueprint $table)
		{
			$table->string('typeVoucher',5)->primary();
			$table->string('description',100);
			$table->date('validity_start')->nullable();
			$table->date('validity_end')->nullable();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('cat_type_bills');
	}
}
