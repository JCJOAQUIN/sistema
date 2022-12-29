<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePrenominasTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('prenominas', function (Blueprint $table)
		{
			$table->increments('idprenomina');
			$table->text('title')->nullable();
			$table->date('datetitle')->nullable();
			$table->string('idCatTypePayroll',5)->nullable();
			$table->tinyInteger('status')->default(2);
			$table->integer('kind')->nullable();
			$table->datetime('date');
			$table->foreign('idCatTypePayroll')->references('id')->on('cat_type_payrolls');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('prenominas');
	}
}
