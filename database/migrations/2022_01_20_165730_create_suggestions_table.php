<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSuggestionsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('suggestions', function (Blueprint $table)
		{
			$table->increments('idSuggestions');
			$table->text('subject')->nullable();
			$table->text('suggestion')->nullable();
			$table->integer('idUsers')->unsigned()->nullable();
			$table->timestamp('date')->nullable();
			$table->timestamp('updated_at')->nullable();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('suggestions');
	}
}
