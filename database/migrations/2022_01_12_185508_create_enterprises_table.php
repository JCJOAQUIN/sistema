<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEnterprisesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('enterprises', function (Blueprint $table)
		{
			$table->increments('id');
			$table->string('name');
			$table->string('rfc');
			$table->text('details');
			$table->string('address');
			$table->string('number',45)->nullable();
			$table->string('colony',45)->nullable();
			$table->mediumText('postalCode')->nullable();
			$table->text('city')->nullable();
			$table->string('phone',20)->nullable();
			$table->integer('state_idstate')->unsigned()->nullable();
			$table->text('path')->nullable();
			$table->string('taxRegime',5);
			$table->enum('status',['ACTIVE','INACTIVE']);
			$table->string('noCertificado',25)->nullable();
			$table->integer('folioBill')->default(0);
			$table->timestamps();
			$table->foreign('state_idstate')->references('idstate')->on('states');
			$table->foreign('taxRegime')->references('taxRegime')->on('cat_tax_regimes');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('enterprises');
	}
}
