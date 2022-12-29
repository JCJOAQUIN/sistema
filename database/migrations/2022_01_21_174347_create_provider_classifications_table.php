<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProviderClassificationsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('provider_classifications', function (Blueprint $table)
		{
			$table->increments('id');
			$table->integer('provider_id')->unsigned();
			$table->integer('provider_data_id')->unsigned()->nullable();
			$table->tinyInteger('classification');
			$table->text('commentary');
			$table->integer('created_by')->unsigned();
			$table->timestamps();
			$table->foreign('provider_id')->references('idProvider')->on('providers');
			$table->foreign('created_by')->references('id')->on('users');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('provider_classifications');
	}
}
