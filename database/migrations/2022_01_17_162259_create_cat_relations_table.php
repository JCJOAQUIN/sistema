<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCatRelationsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('cat_relations', function (Blueprint $table)
		{
			$table->string('typeRelation',5)->primary();
			$table->string('description',100);
			$table->date('validity_start')->nullable();
			$table->date('validity_end')->nullable();
			$table->tinyInteger('cfdi_3_3');
			$table->tinyInteger('cfdi_4_0');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('cat_relations');
	}
}
