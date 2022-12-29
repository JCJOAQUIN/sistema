<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateObraProgramConceptsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('obra_program_concepts', function (Blueprint $table)
		{
			$table->increments('id');
			$table->integer('idUpload')->unsigned()->nullable();
			$table->integer('father')->unsigned()->nullable();
			$table->text('code');
			$table->text('concept');
			$table->string('measurement',200)->nullable();
			$table->foreign('idUpload')->references('id')->on('obra_program_uploads');
			$table->foreign('father')->references('id')->on('obra_program_concepts');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('obra_program_concepts');
	}
}
