<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRequisitionsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('requisitions', function (Blueprint $table)
		{
			$table->increments('id');
			$table->integer('idFolio')->unsigned()->nullable();
			$table->integer('idKind')->unsigned()->nullable();
			$table->text('title')->nullable();
			$table->text('number')->nullable();
			$table->date('date_request')->nullable();
			$table->date('date_comparation')->nullable();
			$table->date('date_obra')->nullable();
			$table->tinyInteger('urgent')->nullable();
			$table->integer('code_wbs')->unsigned()->nullable();
			$table->integer('code_edt')->unsigned()->nullable();
			$table->text('request_requisition')->nullable();
			$table->integer('requisition_type')->unsigned()->nullable();
			$table->string('buy_rent',100)->nullable();
			$table->string('validity',500)->nullable();
			$table->string('generated_number',30)->nullable();
			$table->string('subcontract_number',255)->nullable();
			$table->foreign('idFolio')->references('folio')->on('request_models');
			$table->foreign('idKind')->references('idrequestkind')->on('request_kinds');
			$table->foreign('code_wbs')->references('id')->on('cat_code_w_bs');
			$table->foreign('code_edt')->references('id')->on('cat_code_e_d_ts');
			$table->foreign('requisition_type')->references('id')->on('requisition_types');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('requisitions');
	}
}
