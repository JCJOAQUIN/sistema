<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRequestHasLabelsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('request_has_labels', function (Blueprint $table)
		{
			$table->integer('request_folio')->unsigned();
			$table->integer('request_kind')->unsigned();
			$table->integer('labels_idlabels')->unsigned();
			$table->foreign('request_folio')->references('folio')->on('request_models');
			$table->foreign('request_kind')->references('idrequestkind')->on('request_kinds');
			$table->foreign('labels_idlabels')->references('idlabels')->on('labels');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('request_has_labels');
	}
}
