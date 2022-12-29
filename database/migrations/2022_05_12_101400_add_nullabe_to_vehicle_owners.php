<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNullabeToVehicleOwners extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vehicle_owners', function (Blueprint $table)
		{
            $table->string('email',200)->nullable()->change();
			$table->string('street',200)->nullable()->change();
			$table->string('number',200)->nullable()->change();
			$table->string('colony',200)->nullable()->change();
			$table->string('cp',200)->nullable()->change();
			$table->string('city',200)->nullable()->change();
			$table->integer('state_id')->unsigned()->nullable()->change();
		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vehicle_owners', function (Blueprint $table)
		{
            $table->string('email',200)->nullable(false)->change();
			$table->string('street',200)->nullable(false)->change();
			$table->string('number',200)->nullable(false)->change();
			$table->string('colony',200)->nullable(false)->change();
			$table->string('cp',200)->nullable(false)->change();
			$table->string('city',200)->nullable(false)->change();
		});
    }
}
